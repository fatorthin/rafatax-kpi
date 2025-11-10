<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Filament\Forms;
use Filament\Tables;
use App\Models\LogBook;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\LogBookResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LogBookResource\RelationManagers;
use Filament\Notifications\Notification;

class LogBookResource extends Resource
{
    protected static ?string $model = LogBook::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Histori Log Book';

    protected static ?string $navigationGroup = 'Menu KPI';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('staff_id')
                    ->relationship(
                        name: 'staff',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query) {
                            $panel = Filament::getCurrentPanel();
                            if ($panel && $panel->getId() === 'staff') {
                                $userStaffId = Auth::user()?->staff_id;
                                if ($userStaffId) {
                                    $query->where('id', $userStaffId);
                                }
                            }
                        },
                    )
                    ->default(fn() => Auth::user()?->staff_id)
                    ->disabled(fn() => Filament::getCurrentPanel()?->getId() === 'staff')
                    ->dehydrated()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        // Reset job_description_id when staff changes
                        $set('job_description_id', null);
                    }),
                Forms\Components\Select::make('job_description_id')
                    ->label('Job Description')
                    ->options(function ($get) {
                        $staffId = $get('staff_id');
                        if (!$staffId) {
                            return [];
                        }
                        // Ambil staff yang dipilih
                        $staff = \App\Models\Staff::find($staffId);
                        // Ambil job description yang position_id-nya sama dengan position_reference_id staff
                        return \App\Models\JobDescription::where('position_id', $staff->position_reference_id)
                            ->pluck('job_description', 'id');
                    })
                    ->disabled(fn(Forms\Get $get) => !$get('staff_id'))
                    ->required()
                    ->preload()
                    ->live(),
                Forms\Components\DatePicker::make('date')
                    ->required(),
                Forms\Components\TextInput::make('count_task')
                    ->default(1)
                    ->readOnly()
                    ->visible(fn() => Auth::user()?->hasRole('admin')),
                Forms\Components\Textarea::make('description')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('staff.name')
                    ->label('Staff')
                    ->visible(fn(): bool => Filament::getCurrentPanel()?->getId() === 'admin'),
                Tables\Columns\TextColumn::make('date')
                    ->dateTime('d-m-Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jobDescription.job_description')
                    ->wrap(),
                Tables\Columns\TextColumn::make('description')
                    ->wrap(),
                Tables\Columns\TextColumn::make('count_task')
                    ->sortable(),
                Tables\Columns\TextColumn::make('comment')
                    ->wrap(),
                Tables\Columns\ToggleColumn::make('is_approved')
                    ->label('Disetujui')
                    ->onIcon('heroicon-o-check-circle')
                    ->offIcon('heroicon-o-x-circle')
                    ->onColor('success')
                    ->offColor('danger'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('To'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['date_from'] ?? null) {
                            $indicators['date_from'] = 'From ' . Carbon::parse($data['date_from'])->toFormattedDateString();
                        }
                        if ($data['date_to'] ?? null) {
                            $indicators['date_to'] = 'To ' . Carbon::parse($data['date_to'])->toFormattedDateString();
                        }
                        return $indicators;
                    }),
                Tables\Filters\SelectFilter::make('staff_id')
                    ->relationship('staff', 'name')
                    ->label('Staff'),
                Tables\Filters\SelectFilter::make('job_description_id')
                    ->relationship('jobDescription', 'job_description')
                    ->label('Job Description'),
            ])
            ->actions([
                // Admin-only modal action to input a comment for the log book record
                Tables\Actions\Action::make('add_comment')
                    ->label(fn(?LogBook $record): string => ($record && $record->comment) ? 'Update Comment' : 'Add Comment')
                    ->modalHeading(fn(?LogBook $record): string => ($record && $record->comment) ? 'Update Comment' : 'Add Comment')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->form([
                        Forms\Components\Textarea::make('comment')
                            ->label('Comment')
                            ->required()
                            ->default(fn(?LogBook $record) => $record?->comment),
                    ])
                    ->action(function (LogBook $record, array $data): void {
                        // server-side guard: only admins can save comments
                        if (! (Auth::user()?->hasRole('admin') ?? false)) {
                            Notification::make()
                                ->danger()
                                ->title('Unauthorized')
                                ->body('You are not authorized to perform this action.')
                                ->send();
                            return;
                        }

                        $record->update(['comment' => $data['comment']]);
                        Notification::make()
                            ->success()
                            ->title('Comment saved')
                            ->send();
                    })
                    ->visible(fn(): bool => Auth::user()?->hasRole('admin') ?? false),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLogBooks::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('logbook.viewAny');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
