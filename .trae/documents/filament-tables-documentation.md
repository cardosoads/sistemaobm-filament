# Documentação Completa - Tabelas do Filament PHP

## 1. Visão Geral das Tabelas

As tabelas são um padrão comum de UI para exibir listas de registros em aplicações web. <mcreference link="https://filamentphp.com/docs/4.x/tables/overview" index="0">0</mcreference> O Filament fornece uma API baseada em PHP para definir tabelas com muitos recursos, sendo extremamente customizável.

### Características Principais

* API PHP fluente para definição de tabelas

* Integração nativa com Eloquent ORM

* Suporte a relacionamentos

* Filtros avançados

* Ações personalizáveis

* Paginação automática

* Busca global e por colunas

## 2. Definição de Colunas

### 2.1 Estrutura Básica

As colunas são a base de qualquer tabela. <mcreference link="https://filamentphp.com/docs/4.x/tables/overview" index="0">0</mcreference> O Filament usa Eloquent para obter os dados das linhas da tabela, e você é responsável por definir as colunas usadas em cada linha.

```php
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

public function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('title'),
            TextColumn::make('slug'),
            IconColumn::make('is_featured')
                ->boolean(),
        ]);
}
```

### 2.2 Tipos de Colunas Disponíveis

#### TextColumn

```php
TextColumn::make('name')
    ->searchable()
    ->sortable()
    ->label('Nome')
    ->description('Nome completo do cliente')
    ->limit(50)
    ->tooltip('Clique para ver mais detalhes');
```

#### ImageColumn

```php
ImageColumn::make('avatar')
    ->circular()
    ->size(40)
    ->defaultImageUrl('/images/default-avatar.png');
```

#### IconColumn

```php
IconColumn::make('is_active')
    ->boolean()
    ->trueIcon('heroicon-o-check-badge')
    ->falseIcon('heroicon-o-x-mark')
    ->trueColor('success')
    ->falseColor('danger');
```

#### TagsColumn

```php
TagsColumn::make('tags.name')
    ->limit(3)
    ->separator(',');
```

### 2.3 Colunas Pesquisáveis e Ordenáveis

<mcreference link="https://filamentphp.com/docs/4.x/tables/overview" index="0">0</mcreference> Você pode facilmente modificar colunas encadeando métodos. Por exemplo, pode tornar uma coluna pesquisável usando o método `searchable()`:

```php
TextColumn::make('title')
    ->searchable()
    ->sortable();
```

### 2.4 Acessando Dados de Relacionamentos

<mcreference link="https://filamentphp.com/docs/4.x/tables/overview" index="0">0</mcreference> Você também pode exibir dados em uma coluna que pertence a um relacionamento usando "dot notation":

```php
TextColumn::make('author.name')
    ->label('Autor');
```

### 2.5 Adicionando Colunas a Configurações Existentes

<mcreference link="https://filamentphp.com/docs/4.x/tables/overview" index="0">0</mcreference> O Filament fornece o método `pushColumns()` para adicionar colunas a uma configuração existente sem sobrescrever completamente:

```php
Table::configureUsing(function (Table $table) {
    $table
        ->pushColumns([
            TextColumn::make('created_at')
                ->label('Criado em')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ]);
});
```

## 3. Filtros

### 3.1 Filtros Básicos

<mcreference link="https://filamentphp.com/docs/4.x/tables/overview" index="0">0</mcreference> Os filtros permitem que os usuários filtrem linhas na tabela de outras maneiras além da pesquisa por colunas:

```php
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

public function table(Table $table): Table
{
    return $table
        ->filters([
            Filter::make('is_featured')
                ->query(fn (Builder $query) => $query->where('is_featured', true)),
            SelectFilter::make('status')
                ->options([
                    'draft' => 'Rascunho',
                    'reviewing' => 'Em Revisão',
                    'published' => 'Publicado',
                ]),
        ]);
}
```

### 3.2 Tipos de Filtros

#### Filter (Filtro Básico)

```php
Filter::make('is_active')
    ->label('Apenas Ativos')
    ->query(fn (Builder $query) => $query->where('is_active', true))
    ->toggle()
    ->default();
```

#### SelectFilter

```php
SelectFilter::make('status')
    ->options([
        'active' => 'Ativo',
        'inactive' => 'Inativo',
        'pending' => 'Pendente',
    ])
    ->multiple()
    ->searchable();
```

#### TernaryFilter

```php
TernaryFilter::make('email_verified_at')
    ->label('Email Verificado')
    ->nullable()
    ->placeholder('Todos')
    ->trueLabel('Verificado')
    ->falseLabel('Não Verificado')
    ->nullLabel('Todos');
```

#### Filtros de Relacionamento

```php
SelectFilter::make('author')
    ->relationship('author', 'name')
    ->searchable()
    ->preload();
```

### 3.3 Layout dos Filtros

```php
// Filtros em modal
->filters([...], layout: FiltersLayout::Modal)

// Filtros acima do conteúdo
->filters([...], layout: FiltersLayout::AboveContent)

// Filtros abaixo do conteúdo
->filters([...], layout: FiltersLayout::BelowContent)

// Configurações adicionais
->filtersFormColumns(3)
->filtersFormWidth(MaxWidth::FourExtraLarge)
->filtersFormMaxHeight('400px')
```

## 4. Ações

### 4.1 Ações de Linha (Row Actions)

<mcreference link="https://filamentphp.com/docs/3.x/tables/actions" index="1">1</mcreference> Botões de ação podem ser renderizados no final de cada linha da tabela:

```php
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

public function table(Table $table): Table
{
    return $table
        ->actions([
            EditAction::make(),
            Action::make('view')
                ->url(fn ($record) => route('clients.show', $record))
                ->openUrlInNewTab()
                ->icon('heroicon-o-eye'),
            DeleteAction::make()
                ->requiresConfirmation(),
        ]);
}
```

### 4.2 Ações em Massa (Bulk Actions)

<mcreference link="https://filamentphp.com/docs/3.x/tables/actions" index="1">1</mcreference> As tabelas também suportam "ações em massa" que podem ser usadas quando o usuário seleciona linhas na tabela:

```php
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Collection;

public function table(Table $table): Table
{
    return $table
        ->bulkActions([
            BulkAction::make('activate')
                ->label('Ativar Selecionados')
                ->icon('heroicon-o-check')
                ->requiresConfirmation()
                ->action(fn (Collection $records) => 
                    $records->each->update(['is_active' => true])
                ),
            DeleteBulkAction::make(),
        ]);
}
```

### 4.3 Ações de Cabeçalho (Header Actions)

```php
use Filament\Tables\Actions\CreateAction;

public function table(Table $table): Table
{
    return $table
        ->headerActions([
            CreateAction::make()
                ->label('Novo Cliente')
                ->icon('heroicon-o-plus'),
        ]);
}
```

### 4.4 Ações Personalizadas

```php
Action::make('sendEmail')
    ->label('Enviar Email')
    ->icon('heroicon-o-envelope')
    ->form([
        TextInput::make('subject')->required(),
        RichEditor::make('body')->required(),
    ])
    ->action(function (array $data, $record) {
        // Lógica para enviar email
        Mail::to($record->email)->send(new CustomEmail($data));
    })
    ->requiresConfirmation()
    ->modalHeading('Enviar Email para Cliente')
    ->modalDescription('Preencha os campos abaixo para enviar um email.')
    ->modalSubmitActionLabel('Enviar');
```

## 5. Paginação e Configurações Avançadas

### 5.1 Configurações de Paginação

```php
public function table(Table $table): Table
{
    return $table
        ->paginated([10, 25, 50, 100, 'all'])
        ->defaultPaginationPageOption(25)
        ->extremePaginationLinks()
        ->queryStringIdentifier('clients');
}
```

### 5.2 Busca Global

```php
public function table(Table $table): Table
{
    return $table
        ->searchable() // Habilita busca global
        ->searchOnBlur(); // Busca apenas quando o campo perde o foco
}
```

### 5.3 URLs de Registros (Linhas Clicáveis)

```php
public function table(Table $table): Table
{
    return $table
        ->recordUrl(fn ($record) => route('clients.show', $record))
        ->openRecordUrlInNewTab();
}
```

### 5.4 Reordenação de Registros

```php
public function table(Table $table): Table
{
    return $table
        ->reorderable('sort_order')
        ->paginatedWhileReordering();
}
```

### 5.5 Persistência de Estado

```php
public function table(Table $table): Table
{
    return $table
        ->persistFiltersInSession()
        ->persistSortInSession()
        ->persistSearchInSession()
        ->persistColumnSearchesInSession();
}
```

## 6. Exemplos Práticos de Implementação

### 6.1 Tabela Completa de Clientes

```php
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkAction;

public function table(Table $table): Table
{
    return $table
        ->columns([
            ImageColumn::make('avatar')
                ->label('Foto')
                ->circular()
                ->size(40)
                ->defaultImageUrl('/images/default-avatar.png'),
            
            TextColumn::make('name')
                ->label('Nome')
                ->searchable()
                ->sortable()
                ->description(fn ($record) => $record->email),
            
            TextColumn::make('company')
                ->label('Empresa')
                ->searchable()
                ->sortable()
                ->toggleable(),
            
            TextColumn::make('phone')
                ->label('Telefone')
                ->searchable()
                ->toggleable(),
            
            IconColumn::make('is_active')
                ->label('Status')
                ->boolean()
                ->trueIcon('heroicon-o-check-badge')
                ->falseIcon('heroicon-o-x-mark')
                ->trueColor('success')
                ->falseColor('danger'),
            
            TextColumn::make('created_at')
                ->label('Criado em')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            TernaryFilter::make('is_active')
                ->label('Status')
                ->placeholder('Todos')
                ->trueLabel('Ativo')
                ->falseLabel('Inativo'),
            
            SelectFilter::make('company')
                ->label('Empresa')
                ->options(fn () => Client::distinct()->pluck('company', 'company'))
                ->searchable(),
        ])
        ->actions([
            EditAction::make()
                ->label('Editar'),
            DeleteAction::make()
                ->label('Excluir')
                ->requiresConfirmation(),
        ])
        ->bulkActions([
            BulkAction::make('activate')
                ->label('Ativar Selecionados')
                ->icon('heroicon-o-check')
                ->action(fn ($records) => $records->each->update(['is_active' => true])),
            
            BulkAction::make('deactivate')
                ->label('Desativar Selecionados')
                ->icon('heroicon-o-x-mark')
                ->action(fn ($records) => $records->each->update(['is_active' => false])),
        ])
        ->defaultSort('created_at', 'desc')
        ->paginated([10, 25, 50])
        ->searchable()
        ->persistFiltersInSession();
}
```

### 6.2 Tabela com Relacionamentos

```php
public function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('name')
                ->label('Nome')
                ->searchable(),
            
            TextColumn::make('orders_count')
                ->label('Total de Pedidos')
                ->counts('orders')
                ->sortable(),
            
            TextColumn::make('orders.total')
                ->label('Valor Total')
                ->sum('orders', 'total')
                ->money('BRL'),
            
            TextColumn::make('lastOrder.created_at')
                ->label('Último Pedido')
                ->dateTime('d/m/Y')
                ->sortable(),
        ])
        ->filters([
            SelectFilter::make('orders')
                ->relationship('orders', 'status')
                ->label('Status do Pedido')
                ->multiple(),
        ]);
}
```

## 7. Boas Práticas

### 7.1 Performance

1. **Use eager loading para relacionamentos:**

```php
protected function getTableQuery(): Builder
{
    return parent::getTableQuery()->with(['orders', 'company']);
}
```

1. **Limite o número de registros por página:**

```php
->defaultPaginationPageOption(25)
->paginated([10, 25, 50]) // Evite opções muito altas
```

1. **Use índices no banco de dados para colunas pesquisáveis e ordenáveis**

### 7.2 UX/UI

1. **Forneça labels descritivos:**

```php
TextColumn::make('created_at')
    ->label('Data de Criação')
    ->description('Quando o registro foi criado');
```

1. **Use toggleable para colunas opcionais:**

```php
TextColumn::make('internal_notes')
    ->toggleable(isToggledHiddenByDefault: true);
```

1. **Agrupe ações relacionadas:**

```php
->actions([
    ActionGroup::make([
        EditAction::make(),
        ViewAction::make(),
        DeleteAction::make(),
    ])->label('Ações'),
]);
```

### 7.3 Manutenibilidade

1. **Extraia lógica complexa para métodos separados:**

```php
protected function getStatusColumn(): IconColumn
{
    return IconColumn::make('status')
        ->label('Status')
        ->icon(fn ($state) => match($state) {
            'active' => 'heroicon-o-check-badge',
            'inactive' => 'heroicon-o-x-mark',
            default => 'heroicon-o-question-mark-circle',
        });
}
```

1. **Use constantes para opções de filtros:**

```php
class ClientStatus
{
    const ACTIVE = 'active';
    const INACTIVE = 'inactive';
    const PENDING = 'pending';
    
    public static function options(): array
    {
        return [
            self::ACTIVE => 'Ativo',
            self::INACTIVE => 'Inativo',
            self::PENDING => 'Pendente',
        ];
    }
}
```

### 7.4 Acessibilidade

1. **Sempre forneça labels apropriados**
2. **Use cores com contraste adequado**
3. **Forneça tooltips para ícones**
4. **Teste com leitores de tela**

## 8. Configurações Avançadas

### 8.1 Customização de Query

```php
protected function getTableQuery(): Builder
{
    return parent::getTableQuery()
        ->where('is_deleted', false)
        ->with(['company', 'orders'])
        ->withCount('orders');
}
```

### 8.2 Eventos de Tabela

```php
protected function getTableRecordUrlUsing(): ?Closure
{
    return fn ($record) => route('clients.show', $record);
}

protected function getTableRecordActionUsing(): ?Closure
{
    return fn ($record) => $this->redirect(route('clients.edit', $record));
}
```

### 8.3 Customização de Estilos

```php
public function table(Table $table): Table
{
    return $table
        ->striped()
        ->contentGrid([
            'md' => 2,
            'xl' => 3,
        ])
        ->emptyStateHeading('Nenhum cliente encontrado')
        ->emptyStateDescription('Comece criando seu primeiro cliente.')
        ->emptyStateIcon('heroicon-o-users');
}
```

***

Esta documentação fornece uma base sólida para trabalhar com tabelas no Filament PHP. Para informações mais detalhadas, consulte a documentação oficial em <https://filamentphp.com/docs/tables>.
