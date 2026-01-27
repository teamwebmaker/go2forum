<?php

namespace App\Filament\Resources\PublicDocuments\Tables;

use App\Filament\Resources\PublicDocuments\PublicDocumentResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PublicDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(PublicDocumentResource::labelFor('name'))
                    ->searchable(),
                TextColumn::make('document')
                    ->label(PublicDocumentResource::labelFor('document'))
                    ->searchable(),
                TextColumn::make('link')
                    ->label(PublicDocumentResource::labelFor('link'))
                    ->searchable(),
                IconColumn::make('visibility')
                    ->label(PublicDocumentResource::labelFor('visibility'))
                    ->boolean(),
                TextColumn::make('order')
                    ->label(PublicDocumentResource::labelFor('order'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(PublicDocumentResource::labelFor('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(PublicDocumentResource::labelFor('updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading(__('models.public_documents.actions.delete.headingBulk')),
                ]),
            ]);
    }
}
