<?php

namespace App\Filament\Resources\PublicDocumentUserViews\Tables;

use App\Filament\Resources\PublicDocumentUserViews\PublicDocumentUserViewResource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PublicDocumentUserViewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label(PublicDocumentUserViewResource::labelFor('id'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('document.name')
                    ->label(PublicDocumentUserViewResource::labelFor('public_document_id'))
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('user.nickname')
                    ->label(PublicDocumentUserViewResource::labelFor('user_nickname'))
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('user.full_name')
                    ->label(PublicDocumentUserViewResource::labelFor('user_id'))
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label(PublicDocumentUserViewResource::labelFor('created_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(PublicDocumentUserViewResource::labelFor('updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('public_document_id')
                    ->label(PublicDocumentUserViewResource::labelFor('public_document_id'))
                    ->relationship('document', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('user_id')
                    ->label(PublicDocumentUserViewResource::labelFor('user_id'))
                    ->relationship(
                        'user',
                        'nickname',
                        fn($query) => $query
                            ->whereNotNull('nickname')
                            ->where('nickname', '!=', '')
                    )
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('restricted_document')
                    ->label(__('models.public_documents.fields.requires_auth_to_view'))
                    ->placeholder(__('models.public_document_user_views.filters.all'))
                    ->trueLabel(__('models.public_document_user_views.filters.restricted_only'))
                    ->falseLabel(__('models.public_document_user_views.filters.non_restricted_only'))
                    ->queries(
                        true: fn($query) => $query->whereHas('document', fn($documentQuery) => $documentQuery->where('requires_auth_to_view', true)),
                        false: fn($query) => $query->whereHas('document', fn($documentQuery) => $documentQuery->where('requires_auth_to_view', false)),
                        blank: fn($query) => $query,
                    ),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
