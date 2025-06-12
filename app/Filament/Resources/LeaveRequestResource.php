<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveRequestResource\Pages;
use App\Models\LeaveRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Demandes de congés';
    protected static ?string $navigationGroup = 'Gestion des congés';
    protected static ?int $navigationSort = 1;
    protected static ?string $label = 'Demandes de congés';

    // Les employés peuvent voir cette ressource, mais pas les admins pour créer leurs propres demandes
    public static function canCreate(): bool
    {
        return !Auth::user()?->isAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Employé')
                    ->relationship('user', 'name')
                    ->default(Auth::id())
                    ->disabled() // Toujours désactivé car seuls les employés créent pour eux-mêmes
                    ->visible(fn () => !Auth::user()->isAdmin()) // Invisible pour les admins
                    ->required(),

                Forms\Components\Select::make('type')
                    ->label('Type de congé')
                    ->options([
                        'vacation' => __('leaves.types.vacation'),
                        'sick' => __('leaves.types.sick'),
                        'personal' => __('leaves.types.personal'),
                        'maternity' => __('leaves.types.maternity'),
                        'paternity' => __('leaves.types.paternity'),
                    ])
                    ->required(),

                Forms\Components\DatePicker::make('start_date')
                    ->label(__('leaves.fields.start_date'))
                    ->required()
                    ->minDate(now()),

                Forms\Components\DatePicker::make('end_date')
                    ->label(__('leaves.fields.end_date'))
                    ->required()
                    ->after('start_date'),

                Forms\Components\Textarea::make('reason')
                    ->label(__('leaves.fields.reason'))
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Select::make('status')
                    ->label(__('leaves.fields.status'))
                    ->options([
                        'pending' => __('leaves.status.pending'),
                        'approved' => __('leaves.status.approved'),
                        'rejected' => __('leaves.status.rejected'),
                    ])
                    ->default('pending')
                    ->disabled(fn () => !Auth::user()->isAdmin()),

                Forms\Components\Textarea::make('admin_comment')
                    ->label(__('leaves.fields.admin_comment'))
                    ->visible(fn () => Auth::user()->isAdmin())
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('leaves.fields.employee'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('leaves.fields.type'))
                    ->formatStateUsing(fn (string $state): string => __('leaves.types.' . $state)),

                Tables\Columns\TextColumn::make('start_date')
                    ->label(__('leaves.fields.start_date'))
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label(__('leaves.fields.end_date'))
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('days_requested')
                    ->label(__('leaves.fields.days_requested'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('leaves.fields.status'))
                    ->formatStateUsing(fn (string $state): string => __('leaves.status.' . $state))
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('leaves.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('leaves.fields.status'))
                    ->options([
                        'pending' => __('leaves.status.pending'),
                        'approved' => __('leaves.status.approved'),
                        'rejected' => __('leaves.status.rejected'),
                    ]),

                Tables\Filters\SelectFilter::make('type')
                    ->label(__('leaves.fields.type'))
                    ->options([
                        'vacation' => __('leaves.types.vacation'),
                        'sick' => __('leaves.types.sick'),
                        'personal' => __('leaves.types.personal'),
                        'maternity' => __('leaves.types.maternity'),
                        'paternity' => __('leaves.types.paternity'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(__('leaves.actions.edit'))
                    ->visible(fn (LeaveRequest $record): bool =>
                        !Auth::user()->isAdmin() && $record->status === 'pending'
                    ),
                Tables\Actions\ViewAction::make()->label(__('leaves.actions.view')),

                Action::make('approve')
                    ->label(__('leaves.actions.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (LeaveRequest $record): bool =>
                        Auth::user()->isAdmin() && $record->status === 'pending'
                    )
                    ->action(function (LeaveRequest $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => Auth::id(),
                            'approved_at' => now(),
                        ]);
                    })
                    ->successNotificationTitle('Demande approuvée avec succès'),

                Action::make('reject')
                    ->label(__('leaves.actions.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (LeaveRequest $record): bool =>
                        Auth::user()->isAdmin() && $record->status === 'pending'
                    )
                    ->form([
                        Forms\Components\Textarea::make('admin_comment')
                            ->label(__('leaves.fields.reject_reason'))
                            ->required(),
                    ])
                    ->action(function (LeaveRequest $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'admin_comment' => $data['admin_comment'],
                            'approved_by' => Auth::id(),
                            'approved_at' => now(),
                        ]);
                    })
                    ->successNotificationTitle('Demande rejetée'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Supprimer la sélection')
                        ->visible(fn (): bool => Auth::user()->isAdmin()),
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                if (!Auth::user()->isAdmin()) {
                    return $query->where('user_id', Auth::id());
                }
                return $query;
            });
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveRequests::route('/'),
            'create' => Pages\CreateLeaveRequest::route('/create'),
            'edit' => Pages\EditLeaveRequest::route('/{record}/edit'),
        ];
    }
}
