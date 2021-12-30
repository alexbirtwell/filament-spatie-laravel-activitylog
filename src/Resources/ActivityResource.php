<?php

namespace RyanChandler\FilamentSpatieLaravelActivitylog\Resources;

use RyanChandler\FilamentSpatieLaravelActivitylog\Resources\ActivityResource\Pages;
use Spatie\Activitylog\Models\Activity;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\TablesServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use RyanChandler\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use RyanChandler\FilamentSpatieLaravelActivitylog\RelationManagers\ActivitiesRelationManager;
use RyanChandler\FilamentSpatieLaravelActivitylog\ResourceFinder;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Subject')
                    ->hidden(fn (Component $livewire) => $livewire instanceof ActivitiesRelationManager)
                    ->getStateUsing(function (Activity $record) {
                        if (! $record->subject || ! $record->subject instanceof IsActivitySubject) {
                            return new HtmlString('&mdash;');
                        }

                        /** @var \RyanChandler\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject */
                        $subject = $record->subject;

                        return $subject->getActivitySubjectDescription($record);
                    })
                    ->url(function (Activity $record) {
                        if (! $record->subject || ! $record->subject instanceof IsActivitySubject) {
                            return;
                        }

                        /** @var class-string<\Filament\Resources\Resource> */
                        $resource = ResourceFinder::find($record->subject::class);

                        if (! $resource) {
                            return;
                        }

                        return $resource::getUrl('edit', ['record' => $record->subject]) ?? null;
                    }, shouldOpenInNewTab: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Log Date & Time')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('Has Subject')
                    ->query(fn (Builder $query) => $query->has('subject')),
            ])
            ->defaultSort('created_at', 'DESC');
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
            'index' => Pages\ListActivities::route('/'),
        ];
    }
}
