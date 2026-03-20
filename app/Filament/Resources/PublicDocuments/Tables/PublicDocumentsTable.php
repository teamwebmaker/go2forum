<?php

namespace App\Filament\Resources\PublicDocuments\Tables;

use App\Filament\Resources\PublicDocuments\PublicDocumentResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                    ->limit(30)
                    ->searchable(),
                IconColumn::make('visibility')
                    ->label(PublicDocumentResource::labelFor('visibility'))
                    ->boolean(),
                IconColumn::make('requires_auth_to_view')
                    ->label(PublicDocumentResource::labelFor('requires_auth_to_view'))
                    ->boolean(),
                TextColumn::make('views_count')
                    ->label(PublicDocumentResource::labelFor('views_count'))
                    ->numeric()
                    ->sortable(),
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
                TernaryFilter::make('visibility')
                    ->label(PublicDocumentResource::labelFor('visibility'))
                    ->queries(
                        true: fn(Builder $query) => $query->where('visibility', true),
                        false: fn(Builder $query) => $query->where('visibility', false),
                    ),
                TernaryFilter::make('requires_auth_to_view')
                    ->label(PublicDocumentResource::labelFor('requires_auth_to_view'))
                    ->queries(
                        true: fn(Builder $query) => $query->where('requires_auth_to_view', true),
                        false: fn(Builder $query) => $query->where('requires_auth_to_view', false),
                    ),
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
