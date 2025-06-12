<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Utilisateurs';
    protected static ?string $label = 'Utilisateurs';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?int $navigationSort = 2;

    // Seuls les admins peuvent accéder à cette ressource
    public static function canViewAny(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom complet')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label('Adresse email')
                    ->email()
                    ->required()
                    ->unique(User::class, 'email', ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\TextInput::make('password')
                    ->label('Mot de passe')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->minLength(8)
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->helperText('Laissez vide pour garder le mot de passe actuel lors de la modification'),

                Forms\Components\Select::make('role')
                    ->label('Rôle')
                    ->options([
                        'employee' => 'Employé',
                        'admin' => 'Administrateur',
                    ])
                    ->required()
                    ->default('employee'),

                Forms\Components\TextInput::make('department')
                    ->label('Département')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('role')
                    ->label('Rôle')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Administrateur',
                        'employee' => 'Employé',
                    })
                    ->colors([
                        'primary' => 'admin',
                        'secondary' => 'employee',
                    ]),

                Tables\Columns\TextColumn::make('department')
                    ->label('Département')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('leaveRequests_count')
                    ->label('Demandes de congés')
                    ->counts('leaveRequests')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Rôle')
                    ->options([
                        'admin' => 'Administrateur',
                        'employee' => 'Employé',
                    ]),

                Tables\Filters\SelectFilter::make('department')
                    ->label('Département')
                    ->options(function () {
                        return User::distinct()->pluck('department', 'department')
                            ->filter()->toArray();
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Voir'),
                Tables\Actions\EditAction::make()->label('Modifier'),
                Tables\Actions\DeleteAction::make()
                    ->label('Supprimer')
                    ->before(function (User $record) {
                        if ($record->leaveRequests()->count() > 0) {
                            throw new \Exception('Impossible de supprimer un utilisateur ayant des demandes de congés.');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Supprimer sélection'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
