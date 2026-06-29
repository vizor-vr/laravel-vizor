<?php

namespace Vizor\Laravel\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Vizor\Laravel\Filament\Resources\ContentResource\Pages;
use Vizor\Laravel\Support\FormatEnum;

/**
 * Filament resource for managing Vizor content items via the API.
 */
class ContentResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-film';

    protected static ?string $navigationLabel = 'Content';

    protected static ?string $modelLabel = 'Content';

    protected static ?string $pluralModelLabel = 'Content';

    protected static ?string $slug = 'vizor/content';

    public static function getNavigationGroup(): ?string
    {
        return config('vizor.filament.navigation_group', 'Vizor');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('format')
                    ->options(FormatEnum::labels())
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('src')
                    ->label('Source URL')
                    ->url()
                    ->required()
                    ->maxLength(2048),

                Forms\Components\TextInput::make('poster')
                    ->label('Poster Image URL')
                    ->url()
                    ->maxLength(2048),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('format')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'processing' => 'warning',
                        'error' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('views')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContents::route('/'),
            'create' => Pages\CreateContent::route('/create'),
            'edit' => Pages\EditContent::route('/{record}/edit'),
        ];
    }
}
