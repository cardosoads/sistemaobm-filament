# Documentação Completa do Filament PHP

## 1. Introdução ao Filament PHP

Filament é um framework moderno e poderoso para Laravel que acelera o desenvolvimento de aplicações web. <mcreference link="https://filamentphp.com/" index="3">3</mcreference> É um conjunto de frameworks que se combinam em aplicações dinâmicas, sustentáveis e full-stack com pouco esforço.

### Características Principais

- **Interface moderna e responsiva** construída com Tailwind CSS <mcreference link="https://filamentmastery.com/articles/quick-installation-and-configuration-of-a-filament-panel" index="2">2</mcreference>
- **Configuração rápida** e comandos amigáveis ao desenvolvedor
- **Customizável e extensível** com plugins e configurações avançadas
- **Integração nativa com Livewire** para interfaces dinâmicas
- **Suporte completo a CRUD** para modelos Eloquent

### Componentes do Filament

O Filament é composto por vários pacotes que trabalham juntos:

- **Panel Builder**: Para construir painéis administrativos
- **Form Builder**: Para criar formulários dinâmicos
- **Table Builder**: Para exibir e gerenciar dados tabulares
- **Notifications**: Sistema de notificações
- **Actions**: Botões e ações interativas
- **Infolists**: Listas de informações somente leitura
- **Widgets**: Componentes de dashboard

## 2. Instalação e Configuração

### 2.1 Requisitos

<mcreference link="https://filamentphp.com/docs/3.x/panels/installation" index="1">1</mcreference> O Filament requer:

- PHP 8.1+
- Laravel v10.0+
- Livewire v3.0+

### 2.2 Instalação

#### Passo 1: Configurar Projeto Laravel

<mcreference link="https://filamentmastery.com/articles/quick-installation-and-configuration-of-a-filament-panel" index="2">2</mcreference> Antes de instalar o Filament, você precisa de um projeto Laravel funcionando:

```bash
composer create-project laravel/laravel your-project-name
```

#### Passo 2: Instalar o Filament

<mcreference link="https://filamentphp.com/docs/3.x/panels/installation" index="1">1</mcreference> Instale o Filament Panel Builder executando os seguintes comandos:

```bash
composer require filament/filament:"^3.3" -W
php artisan filament:install --panels
```

<mcreference link="https://filamentphp.com/docs/3.x/panels/installation" index="1">1</mcreference> Isso criará e registrará um novo Laravel service provider chamado `app/Providers/Filament/AdminPanelProvider.php`.

#### Passo 3: Criar Usuário

<mcreference link="https://filamentphp.com/docs/3.x/panels/installation" index="1">1</mcreference> Você pode criar uma nova conta de usuário com o seguinte comando:

```bash
php artisan make:filament-user
```

### 2.3 Configuração do Modelo User

<mcreference link="https://filamentmastery.com/articles/quick-installation-and-configuration-of-a-filament-panel" index="2">2</mcreference> Para permitir acesso de login ao painel Filament, atualize o modelo User:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    public function canAccessPanel(Panel $panel): bool
    {
        return str_ends_with($this->email, '@yourdomain.com');
    }
}
```

### 2.4 Acessando o Painel

<mcreference link="https://filamentphp.com/docs/3.x/panels/installation" index="1">1</mcreference> Abra `/admin` no seu navegador web, faça login e comece a construir sua aplicação!

## 3. Panels (Painéis)

### 3.1 Introdução aos Painéis

<mcreference link="https://filamentphp.com/docs/3.x/panels/configuration" index="4">4</mcreference> Por padrão, quando você instala o pacote, há um painel configurado para você - e ele fica em `/admin`. Todos os recursos, páginas personalizadas e widgets de dashboard que você criar são registrados neste painel.

### 3.2 Criando Múltiplos Painéis

<mcreference link="https://filamentphp.com/docs/3.x/panels/configuration" index="4">4</mcreference> Você pode criar quantos painéis quiser, e cada um pode ter seu próprio conjunto de recursos, páginas e widgets.

```bash
php artisan make:filament-panel app
```

<mcreference link="https://filamentphp.com/docs/3.x/panels/configuration" index="4">4</mcreference> Isso criará um novo painel chamado "app". Um arquivo de configuração será criado em `app/Providers/Filament/AppPanelProvider.php`.

### 3.3 Configuração de Painéis

#### Alterando o Caminho

<mcreference link="https://filamentphp.com/docs/3.x/panels/configuration" index="4">4</mcreference> Em um arquivo de configuração de painel, você pode alterar o caminho onde a aplicação é acessível usando o método `path()`:

```php
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->path('app');
}
```

#### Definindo um Domínio

<mcreference link="https://filamentphp.com/docs/3.x/panels/configuration" index="4">4</mcreference> Por padrão, o Filament responderá a requisições de todos os domínios. Se você quiser limitá-lo a um domínio específico:

```php
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->domain('admin.example.com');
}
```

#### Customizando a Largura Máxima do Conteúdo

<mcreference link="https://filamentphp.com/docs/3.x/panels/configuration" index="4">4</mcreference> Por padrão, o Filament restringirá a largura do conteúdo na página:

```php
use Filament\Panel;
use Filament\Support\Enums\MaxWidth;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->maxContentWidth(MaxWidth::Full);
}
```

## 4. Resources (Recursos)

### 4.1 Introdução aos Resources

<mcreference link="https://filamentphp.com/docs/3.x/panels/resources/getting-started" index="5">5</mcreference> Resources são classes estáticas usadas para construir interfaces CRUD para seus modelos Eloquent. Eles descrevem como os administradores devem ser capazes de interagir com dados da sua aplicação - usando tabelas e formulários.

### 4.2 Criando um Resource

<mcreference link="https://filamentphp.com/docs/3.x/panels/resources/getting-started" index="5">5</mcreference> Para criar um resource para o modelo `App\Models\Customer`:

```bash
php artisan make:filament-resource Customer
```

<mcreference link="https://filamentphp.com/docs/3.x/panels/resources/getting-started" index="5">5</mcreference> Isso criará vários arquivos no diretório `app/Filament/Resources`:

```
.
+-- CustomerResource.php
+-- CustomerResource
|   +-- Pages
|   |   +-- CreateCustomer.php
|   |   +-- EditCustomer.php
|   |   +-- ListCustomers.php
```

### 4.3 Resources Simples (Modal)

<mcreference link="https://filamentphp.com/docs/3.x/panels/resources/getting-started" index="5">5</mcreference> Às vezes, seus modelos são simples o suficiente para que você queira gerenciar registros em uma página, usando modais para criar, editar e excluir registros:

```bash
php artisan make:filament-resource Customer --simple
```

### 4.4 Geração Automática de Formulários e Tabelas

<mcreference link="https://filamentphp.com/docs/3.x/panels/resources/getting-started" index="5">5</mcreference> Se você quiser economizar tempo, o Filament pode gerar automaticamente o formulário e a tabela para você, baseado nas colunas do banco de dados do seu modelo:

```bash
php artisan make:filament-resource Customer --generate
```

### 4.5 Formulários de Resource

<mcreference link="https://filamentphp.com/docs/3.x/panels/resources/getting-started" index="5">5</mcreference> Classes de resource contêm um método `form()` que é usado para construir os formulários nas páginas Create e Edit:

```php
use Filament\Forms;
use Filament\Forms\Form;

public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('email')->email()->required(),
            // ...
        ]);
}
```

### 4.6 Títulos de Registro

<mcreference link="https://filamentphp.com/docs/3.x/panels/resources/getting-started" index="5">5</mcreference> Um `$recordTitleAttribute` pode ser definido para seu resource, que é o nome da coluna no seu modelo que pode ser usado para identificá-lo de outros:

```php
protected static ?string $recordTitleAttribute = 'name';
```

### 4.7 Autorização

<mcreference link="https://filamentphp.com/docs/3.x/panels/resources/getting-started" index="5">5</mcreference> Para autorização, o Filament observará quaisquer políticas de modelo registradas na sua aplicação. Os seguintes métodos são usados:

- `viewAny()` é usado para ocultar completamente resources do menu de navegação
- `view()` é usado para ocultar o botão "View" na tabela
- `create()` é usado para ocultar o botão "New" na tabela
- `update()` é usado para ocultar o botão "Edit" na tabela
- `delete()` é usado para ocultar o botão "Delete" na tabela

## 5. Forms (Formulários)

### 5.1 Introdução aos Formulários

<mcreference link="https://filamentphp.com/docs/3.x/forms/advanced" index="5">5</mcreference> O Filament Form Builder é projetado para ser flexível e customizável. Muitos construtores de formulários existentes permitem que os usuários definam um esquema de formulário, mas não fornecem uma ótima interface para definir interações entre campos ou lógica personalizada.

### 5.2 Componentes de Formulário Básicos

#### TextInput

```php
use Filament\Forms\Components\TextInput;

TextInput::make('name')
    ->required()
    ->maxLength(255)
    ->placeholder('Digite o nome')
    ->helperText('Nome completo do usuário');
```

#### Textarea

```php
use Filament\Forms\Components\Textarea;

Textarea::make('description')
    ->required()
    ->rows(4)
    ->cols(20);
```

#### Select

```php
use Filament\Forms\Components\Select;

Select::make('status')
    ->options([
        'draft' => 'Rascunho',
        'reviewing' => 'Em Revisão',
        'published' => 'Publicado',
    ])
    ->required();
```

#### DatePicker

```php
use Filament\Forms\Components\DatePicker;

DatePicker::make('birth_date')
    ->required()
    ->displayFormat('d/m/Y')
    ->format('Y-m-d');
```

### 5.3 Reatividade em Formulários

<mcreference link="https://filamentphp.com/docs/3.x/forms/advanced" index="5">5</mcreference> O Livewire é uma ferramenta que permite que HTML renderizado pelo Blade seja re-renderizado dinamicamente sem exigir um recarregamento completo da página. Os formulários Filament são construídos sobre o Livewire.

#### Campos Reativos

<mcreference link="https://filamentphp.com/docs/3.x/forms/advanced" index="5">5</mcreference> Por padrão, quando um usuário usa um campo, o formulário não será re-renderizado. Se você deseja re-renderizar o formulário após o usuário ter interagido com um campo, você pode usar o método `live()`:

```php
use Filament\Forms\Components\Select;

Select::make('status')
    ->options([
        'draft' => 'Rascunho',
        'reviewing' => 'Em Revisão',
        'published' => 'Publicado',
    ])
    ->live();
```

#### Campos Reativos no Blur

<mcreference link="https://filamentphp.com/docs/3.x/forms/advanced" index="5">5</mcreference> Você pode querer re-renderizar o formulário apenas depois que o usuário terminou de usar o campo, quando ele sai de foco:

```php
use Filament\Forms\Components\TextInput;

TextInput::make('username')
    ->live(onBlur: true);
```

#### Debouncing em Campos Reativos

<mcreference link="https://filamentphp.com/docs/3.x/forms/advanced" index="5">5</mcreference> Você pode usar "debouncing" para evitar que uma requisição de rede seja enviada até que um usuário tenha parado de digitar por um certo período:

```php
use Filament\Forms\Components\TextInput;

TextInput::make('username')
    ->live(debounce: 500); // Aguarda 500ms antes de re-renderizar
```

### 5.4 Injeção de Utilitários

<mcreference link="https://filamentphp.com/docs/3.x/forms/advanced" index="5">5</mcreference> A grande maioria dos métodos usados para configurar campos e componentes de layout aceitam funções como parâmetros em vez de valores codificados:

#### Injetando o Estado Atual de um Campo

<mcreference link="https://filamentphp.com/docs/3.x/forms/advanced" index="5">5</mcreference> Se você deseja acessar o estado atual (valor) do campo, defina um parâmetro `$state`:

```php
function ($state) {
    // ...
}
```

#### Injetando o Estado de Outro Campo

<mcreference link="https://filamentphp.com/docs/3.x/forms/advanced" index="5">5</mcreference> Você também pode recuperar o estado (valor) de outro campo de dentro de um callback, usando um parâmetro `$get`:

```php
use Filament\Forms\Get;

function (Get $get) {
    $email = $get('email'); // Armazena o valor do campo `email` na variável `$email`
    //...
}
```

## 6. Tables (Tabelas)

### 6.1 Visão Geral das Tabelas

As tabelas são um padrão comum de UI para exibir listas de registros em aplicações web. O Filament fornece uma API baseada em PHP para definir tabelas com muitos recursos, sendo extremamente customizável.

### 6.2 Definição de Colunas

#### Estrutura Básica

As colunas são a base de qualquer tabela. O Filament usa Eloquent para obter os dados das linhas da tabela:

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

#### Tipos de Colunas

**TextColumn**

A coluna de texto é a mais comum e versátil:

```php
TextColumn::make('name')
    ->searchable()
    ->sortable()
    ->label('Nome')
    ->description('Nome completo do cliente')
    ->limit(50)
    ->wrap()
    ->copyable()
    ->copyMessage('Nome copiado!')
    ->copyMessageDuration(1500);
```

**ImageColumn**

Para exibir imagens:

```php
ImageColumn::make('avatar')
    ->circular()
    ->size(40)
    ->defaultImageUrl('/images/default-avatar.png')
    ->disk('public')
    ->visibility('private');
```

**IconColumn**

Para exibir ícones baseados em valores:

```php
IconColumn::make('is_active')
    ->boolean()
    ->trueIcon('heroicon-o-check-badge')
    ->falseIcon('heroicon-o-x-mark')
    ->trueColor('success')
    ->falseColor('danger');
```

**TagsColumn**

Para exibir múltiplas tags:

```php
TagsColumn::make('tags')
    ->separator(',')
    ->limit(3)
    ->limitedRemainingText(isSingular: false);
```

### 6.3 Colunas Pesquisáveis e Ordenáveis

#### Tornando Colunas Pesquisáveis

```php
TextColumn::make('name')
    ->searchable()
    ->searchable(isIndividual: true); // Pesquisa individual por coluna
```

#### Tornando Colunas Ordenáveis

```php
TextColumn::make('created_at')
    ->sortable()
    ->sortable(query: function (Builder $query, string $direction): Builder {
        return $query->orderBy('created_at', $direction);
    });
```

### 6.4 Acessando Dados de Relacionamento

#### Relacionamentos Simples

```php
TextColumn::make('author.name')
    ->label('Autor')
    ->sortable()
    ->searchable();
```

#### Relacionamentos Complexos

```php
TextColumn::make('posts_count')
    ->counts('posts')
    ->label('Total de Posts');

TextColumn::make('latest_post.title')
    ->label('Último Post')
    ->placeholder('Nenhum post');
```

### 6.5 Adicionando Colunas a Configurações Existentes

Para adicionar colunas a uma tabela existente sem sobrescrever as colunas padrão:

```php
use Filament\Tables\Table;

public function table(Table $table): Table
{
    return $table
        ->columns([
            // Colunas existentes...
            ...parent::table($table)->getColumns(),
            // Novas colunas
            TextColumn::make('custom_field')
                ->label('Campo Personalizado'),
        ]);
}
```

### 6.6 Filtros

Os filtros permitem que os usuários filtrem linhas na tabela de forma interativa.

#### Filtros Básicos

```php
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

Filter::make('is_featured')
    ->label('Destacados')
    ->query(fn (Builder $query): Builder => $query->where('is_featured', true))
    ->toggle();
```

#### SelectFilter

```php
use Filament\Tables\Filters\SelectFilter;

SelectFilter::make('status')
    ->label('Status')
    ->options([
        'draft' => 'Rascunho',
        'reviewing' => 'Em Revisão',
        'published' => 'Publicado',
    ])
    ->default('published');
```

#### TernaryFilter

Para filtros com três estados (sim/não/todos):

```php
use Filament\Tables\Filters\TernaryFilter;

TernaryFilter::make('is_featured')
    ->label('Destacado')
    ->boolean()
    ->trueLabel('Apenas destacados')
    ->falseLabel('Não destacados')
    ->native(false);
```

#### Filtros de Relacionamento

```php
SelectFilter::make('author')
    ->relationship('author', 'name')
    ->searchable()
    ->preload()
    ->multiple();
```

### 6.7 Layout de Filtros

#### Filtros em Colunas

```php
use Filament\Tables\Enums\FiltersLayout;

public function table(Table $table): Table
{
    return $table
        ->filters([
            // Seus filtros...
        ], layout: FiltersLayout::AboveContent)
        ->filtersFormColumns(2);
}
```

#### Filtros em Modal

```php
public function table(Table $table): Table
{
    return $table
        ->filters([
            // Seus filtros...
        ], layout: FiltersLayout::Modal)
        ->filtersFormWidth('4xl');
}
```

### 6.8 Actions em Tabelas

#### Actions de Linha

```php
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

public function table(Table $table): Table
{
    return $table
        ->actions([
            EditAction::make(),
            Action::make('activate')
                ->icon('heroicon-o-check')
                ->action(fn ($record) => $record->activate()),
            DeleteAction::make(),
        ]);
}
```

#### Bulk Actions

```php
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\DeleteBulkAction;

public function table(Table $table): Table
{
    return $table
        ->bulkActions([
            DeleteBulkAction::make(),
            BulkAction::make('activate')
                ->icon('heroicon-o-check')
                ->action(fn ($records) => $records->each->activate()),
        ]);
}
```

### 6.9 Paginação e Performance

#### Configurando Paginação

```php
public function table(Table $table): Table
{
    return $table
        ->defaultPaginationPageOption(25)
        ->paginationPageOptions([10, 25, 50, 100]);
}
```

#### Paginação Simples

```php
public function table(Table $table): Table
{
    return $table
        ->simplePagination();
}
```

### 6.10 Customização Avançada

#### Headers Personalizados

```php
TextColumn::make('name')
    ->label('Nome Completo')
    ->description('Nome e sobrenome do usuário');
```

#### Formatação de Dados

```php
TextColumn::make('price')
    ->money('BRL')
    ->sortable();

TextColumn::make('created_at')
    ->dateTime('d/m/Y H:i')
    ->sortable();

TextColumn::make('description')
    ->html()
    ->limit(100);
```

#### Estados Condicionais

```php
TextColumn::make('status')
    ->badge()
    ->color(fn (string $state): string => match ($state) {
        'draft' => 'gray',
        'reviewing' => 'warning',
        'published' => 'success',
        'rejected' => 'danger',
    });
```
```

## 7. Actions (Ações)

### 7.1 O que é uma Action?

<mcreference link="https://filamentphp.com/docs/3.x/actions/overview" index="4">4</mcreference> "Action" é uma palavra que é usada bastante dentro da comunidade Laravel. No Filament, actions também lidam com "fazer" algo na sua aplicação. No entanto, elas são um pouco diferentes das actions tradicionais. Elas são projetadas para serem usadas no contexto de uma interface de usuário.

### 7.2 Exemplo Básico de Action

<mcreference link="https://filamentphp.com/docs/3.x/actions/overview" index="4">4</mcreference> Por exemplo, você pode ter um botão para deletar um registro de cliente, que abre um modal para confirmar sua decisão:

```php
Action::make('delete')
    ->requiresConfirmation()
    ->action(fn () => $this->client->delete());
```

### 7.3 Actions com Formulários

<mcreference link="https://filamentphp.com/docs/3.x/actions/overview" index="4">4</mcreference> Actions também podem coletar informações extras do usuário. Por exemplo, você pode ter um botão para enviar email para um cliente:

```php
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Mail;

Action::make('sendEmail')
    ->form([
        TextInput::make('subject')->required(),
        RichEditor::make('body')->required(),
    ])
    ->action(function (array $data) {
        Mail::to($this->client)
            ->send(new GenericEmail(
                subject: $data['subject'],
                body: $data['body'],
            ));
    });
```

### 7.4 Actions com URLs

<mcreference link="https://filamentphp.com/docs/3.x/actions/overview" index="4">4</mcreference> Actions podem ser muito mais simples e nem precisam de um modal. Você pode passar uma URL para uma action:

```php
Action::make('edit')
    ->url(fn (): string => route('posts.edit', ['post' => $this->post]));
```

### 7.5 Tipos de Actions

<mcreference link="https://filamentphp.com/docs/3.x/actions/overview" index="4">4</mcreference> O conceito de "actions" é usado em todo o Filament em muitos contextos:

#### Actions de Componente Livewire Personalizado

<mcreference link="https://filamentphp.com/docs/3.x/actions/overview" index="4">4</mcreference> Você pode adicionar uma action a qualquer componente Livewire na sua aplicação. Essas actions usam a classe `Filament\Actions\Action`.

#### Actions de Tabela

<mcreference link="https://filamentphp.com/docs/3.x/actions/overview" index="4">4</mcreference> As tabelas do Filament também usam actions. Actions podem ser adicionadas ao final de qualquer linha da tabela, ou mesmo no cabeçalho de uma tabela. Essas actions usam a classe `Filament\Tables\Actions\Action`.

#### Actions de Componente de Formulário

<mcreference link="https://filamentphp.com/docs/3.x/actions/overview" index="4">4</mcreference> Componentes de formulário podem conter actions. Essas actions usam a classe `Filament\Forms\Components\Actions\Action`.

#### Actions de Componente Infolist

<mcreference link="https://filamentphp.com/docs/3.x/actions/overview" index="4">4</mcreference> Componentes infolist podem conter actions. Essas usam a classe `Filament\Infolists\Components\Actions\Action`.

## 8. Widgets (Widgets)

### 8.1 Introdução aos Widgets

Os widgets são componentes que podem ser adicionados ao dashboard ou a outras páginas para exibir informações importantes de forma visual e interativa.

### 8.2 Tipos de Widgets

#### Stats Overview Widget

Widget para exibir estatísticas importantes:

```php
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Usuários Únicos', '192.1k')
                ->description('32k aumento')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Taxa de Rejeição', '21%')
                ->description('7% diminuição')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('Duração Média da Sessão', '3:12')
                ->description('3% aumento')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
        ];
    }
}
```

#### Chart Widget

Widget para exibir gráficos:

```php
use Filament\Widgets\ChartWidget;

class BlogPostsChart extends ChartWidget
{
    protected static ?string $heading = 'Posts do Blog';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Posts criados',
                    'data' => [0, 10, 5, 2, 21, 32, 45, 74, 65, 45, 77, 89],
                ],
            ],
            'labels' => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
```

#### Table Widget

Widget para exibir tabelas:

```php
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected function getTableQuery(): Builder
    {
        return Order::query()->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('id'),
            Tables\Columns\TextColumn::make('customer.name'),
            Tables\Columns\TextColumn::make('total')
                ->money('BRL'),
        ];
    }
}
```

## 9. Infolists (Listas de Informação)

### 9.1 Introdução aos Infolists

<mcreference link="https://filamentphp.com/docs/3.x/infolists/getting-started" index="2">2</mcreference> O pacote infolist do Filament permite que você renderize uma lista somente leitura de dados sobre uma entidade específica. Também é usado dentro de outros pacotes Filament, como o Panel Builder para exibir recursos de aplicação e gerenciadores de relação.

### 9.2 Definindo Entries

<mcreference link="https://filamentphp.com/docs/3.x/infolists/getting-started" index="2">2</mcreference> O primeiro passo para construir um infolist é definir as entries que serão exibidas na lista:

```php
use Filament\Infolists\Components\TextEntry;

$infolist
    ->schema([
        TextEntry::make('title'),
        TextEntry::make('slug'),
        TextEntry::make('content'),
    ]);
```

### 9.3 Usando Componentes de Layout

<mcreference link="https://filamentphp.com/docs/3.x/infolists/getting-started" index="2">2</mcreference> O Infolist Builder permite que você use componentes de layout dentro do array schema para controlar como as entries são exibidas:

```php
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;

[
    TextEntry::make('title'),
    TextEntry::make('slug'),
    Section::make('Media')
        ->description('Imagens usadas no layout da página.')
        ->schema([
            ImageEntry::make('hero_image'),
            TextEntry::make('alt_text'),
        ]),
]
```

### 9.4 Actions em Infolists

<mcreference link="https://filamentphp.com/docs/3.x/infolists/actions" index="1">1</mcreference> Os infolists do Filament podem usar Actions. Eles são botões que podem ser adicionados a qualquer componente infolist:

```php
use App\Actions\ResetStars;
use Filament\Infolists\Components\Actions\Action;

Action::make('resetStars')
    ->icon('heroicon-m-x-mark')
    ->color('danger')
    ->requiresConfirmation()
    ->action(function (ResetStars $resetStars) {
        $resetStars();
    });
```

#### Adicionando Actions Affix a uma Entry

<mcreference link="https://filamentphp.com/docs/3.x/infolists/actions" index="1">1</mcreference> Certas entries suportam "affix actions", que são botões que podem ser colocados antes ou depois do seu conteúdo:

```php
use App\Models\Product;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\TextEntry;

TextEntry::make('cost')
    ->prefix('€')
    ->suffixAction(
        Action::make('copyCostToPrice')
            ->icon('heroicon-m-clipboard')
            ->requiresConfirmation()
            ->action(function (Product $record) {
                $record->price = $record->cost;
                $record->save();
            })
    );
```

## 10. Notifications (Notificações)

### 10.1 Introdução às Notificações

<mcreference link="https://filamentphp.com/docs/3.x/panels/notifications" index="2">2</mcreference> O Panel Builder usa o pacote Notifications para enviar mensagens aos usuários.

### 10.2 Configurando Notificações de Banco de Dados

<mcreference link="https://filamentphp.com/docs/3.x/panels/notifications" index="2">2</mcreference> Se você quiser receber notificações de banco de dados, pode habilitá-las na configuração:

```php
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->databaseNotifications();
}
```

<mcreference link="https://filamentphp.com/docs/3.x/panels/notifications" index="2">2</mcreference> Você também pode controlar o polling de notificações de banco de dados:

```php
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->databaseNotifications()
        ->databaseNotificationsPolling('30s');
}
```

### 10.3 Configurando a Tabela de Notificações

<mcreference link="https://filamentphp.com/docs/3.x/notifications/database-notifications" index="3">3</mcreference> Antes de começar, certifique-se de que a tabela de notificações do Laravel seja adicionada ao seu banco de dados:

```bash
# Laravel 11 e superior
php artisan make:notifications-table

# Laravel 10
php artisan notifications:table
```

### 10.4 Enviando Notificações de Banco de Dados

<mcreference link="https://filamentphp.com/docs/3.x/notifications/database-notifications" index="3">3</mcreference> Há várias maneiras de enviar notificações de banco de dados:

#### Usando a API Fluente

```php
use Filament\Notifications\Notification;

$recipient = auth()->user();

Notification::make()
    ->title('Salvo com sucesso')
    ->sendToDatabase($recipient);
```

#### Usando o Método notify()

```php
use Filament\Notifications\Notification;

$recipient = auth()->user();

$recipient->notify(
    Notification::make()
        ->title('Salvo com sucesso')
        ->toDatabase(),
);
```

### 10.5 Configurando WebSockets

<mcreference link="https://filamentphp.com/docs/3.x/panels/notifications" index="2">2</mcreference> O Panel Builder vem com um nível de suporte integrado para notificações broadcast e de banco de dados em tempo real:

1. Leia sobre broadcasting na documentação do Laravel
2. Instale e configure broadcasting para usar uma integração websockets do lado do servidor como Pusher
3. Publique a configuração do pacote Filament:

```bash
php artisan vendor:publish --tag=filament-config
```

4. Edite a configuração em `config/filament.php` e descomente a seção `broadcasting.echo`
5. Limpe os caches relevantes:

```bash
php artisan route:clear
php artisan config:clear
```

## 11. Navigation (Navegação)

### 11.1 Visão Geral da Navegação

<mcreference link="https://filamentphp.com/docs/3.x/panels/navigation" index="1">1</mcreference> Por padrão, o Filament registrará itens de navegação para cada um dos seus recursos, páginas personalizadas e clusters.

### 11.2 Customizando Itens de Navegação

#### Customizando o Label de um Item

<mcreference link="https://filamentphp.com/docs/3.x/panels/navigation" index="1">1</mcreference> Por padrão, o label de navegação é gerado a partir do nome do resource ou página. Você pode customizar isso usando a propriedade `$navigationLabel`:

```php
protected static ?string $navigationLabel = 'Label de Navegação Personalizado';
```

#### Customizando o Ícone de um Item

<mcreference link="https://filamentphp.com/docs/3.x/panels/navigation" index="1">1</mcreference> Para customizar o ícone de um item de navegação, você pode sobrescrever a propriedade `$navigationIcon`:

```php
protected static ?string $navigationIcon = 'heroicon-o-document-text';
```

#### Ordenando Itens de Navegação

<mcreference link="https://filamentphp.com/docs/3.x/panels/navigation" index="1">1</mcreference> Por padrão, itens de navegação são ordenados alfabeticamente. Você pode customizar isso usando a propriedade `$navigationSort`:

```php
protected static ?int $navigationSort = 3;
```

### 11.3 Adicionando Badges aos Itens

<mcreference link="https://filamentphp.com/docs/3.x/panels/navigation" index="1">1</mcreference> Para adicionar um badge próximo ao item de navegação, você pode usar o método `getNavigationBadge()`:

```php
public static function getNavigationBadge(): ?string
{
    return static::getModel()::count();
}
```

<mcreference link="https://filamentphp.com/docs/3.x/panels/navigation" index="1">1</mcreference> Para estilizar o badge contextualmente, retorne uma cor do método `getNavigationBadgeColor()`:

```php
public static function getNavigationBadgeColor(): ?string
{
    return static::getModel()::count() > 10 ? 'warning' : 'primary';
}
```

### 11.4 Agrupando Itens de Navegação

<mcreference link="https://filamentphp.com/docs/3.x/panels/navigation" index="1">1</mcreference> Você pode agrupar itens de navegação especificando uma propriedade `$navigationGroup`:

```php
protected static ?string $navigationGroup = 'Configurações';
```

### 11.5 Customizando Grupos de Navegação

<mcreference link="https://filamentphp.com/docs/3.x/panels/navigation" index="1">1</mcreference> Você pode customizar grupos de navegação chamando `navigationGroups()` na configuração:

```php
use Filament\Navigation\NavigationGroup;
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->navigationGroups([
            NavigationGroup::make()
                 ->label('Loja')
                 ->icon('heroicon-o-shopping-cart'),
            NavigationGroup::make()
                ->label('Blog')
                ->icon('heroicon-o-pencil'),
            NavigationGroup::make()
                ->label('Configurações')
                ->icon('heroicon-o-cog-6-tooth')
                ->collapsed(),
        ]);
}
```

### 11.6 Navegação Superior

<mcreference link="https://filamentphp.com/docs/3.x/panels/navigation" index="1">1</mcreference> Por padrão, o Filament usará uma navegação lateral. Você pode usar uma navegação superior em vez disso:

```php
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->topNavigation();
}
```

## 12. Configurações Avançadas

### 12.1 Render Hooks

<mcreference link="https://filamentphp.com/docs/3.x/panels/configuration" index="4">4</mcreference> Render hooks permitem que você renderize conteúdo Blade em vários pontos nas views do framework:

```php
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->renderHook(
            PanelsRenderHook::BODY_START,
            fn (): string => Blade::render('@livewire(\'livewire-ui-modal\')'),
        );
}
```

### 12.2 Lifecycle Hooks

<mcreference link="https://filamentphp.com/docs/3.x/panels/configuration" index="4">4</mcreference> Hooks podem ser usados para executar código durante o ciclo de vida de um painel. `bootUsing()` é um hook que é executado em cada requisição que ocorre dentro desse painel:

```php
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->bootUsing(function () {
            // Código personalizado aqui
        });
}
```

### 12.3 Middleware Personalizado

Você pode adicionar middleware personalizado aos painéis:

```php
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->middleware([
            // Middleware padrão...
            \App\Http\Middleware\CustomMiddleware::class,
        ]);
}
```

## 13. Otimização para Produção

### 13.1 Otimizando o Filament para Produção

<mcreference link="https://filamentphp.com/docs/3.x/panels/installation" index="1">1</mcreference> Para otimizar o Filament para produção, você deve executar o seguinte comando no seu script de deploy:

```bash
php artisan filament:optimize
```

<mcreference link="https://filamentphp.com/docs/3.x/panels/installation" index="1">1</mcreference> Este comando fará cache dos componentes Filament e adicionalmente dos ícones Blade, o que pode melhorar significativamente a performance dos seus painéis Filament.

### 13.2 Cache de Componentes Filament

<mcreference link="https://filamentphp.com/docs/3.x/panels/installation" index="1">1</mcreference> Se você não está usando o comando `filament:optimize`, pode considerar executar `php artisan filament:cache-components` no seu script de deploy:

```bash
php artisan filament:cache-components
```

### 13.3 Cache de Ícones Blade

<mcreference link="https://filamentphp.com/docs/3.x/panels/installation" index="1">1</mcreference> Se você não está usando o comando `filament:optimize`, pode considerar executar `php artisan icons:cache`:

```bash
php artisan icons:cache
```

<mcreference link="https://filamentphp.com/docs/3.x/panels/installation" index="1">1</mcreference> Isso é porque o Filament usa o pacote Blade Icons, que pode ser muito mais performático quando em cache.

### 13.4 Habilitando OPcache no Servidor

<mcreference link="https://filamentphp.com/docs/3.x/panels/installation" index="1">1</mcreference> Otimizar o PHP OPcache para produção configurará o OPcache para armazenar seu código PHP compilado na memória para melhorar muito a performance.

### 13.5 Otimizando sua Aplicação Laravel

<mcreference link="https://filamentphp.com/docs/3.x/panels/installation" index="1">1</mcreference> Você também deve considerar otimizar sua aplicação Laravel para produção executando `php artisan optimize` no seu script de deploy:

```bash
php artisan optimize
```

### 13.6 Permitindo Acesso de Usuários a um Painel

<mcreference link="https://filamentphp.com/docs/3.x/panels/installation" index="1">1</mcreference> Por padrão, todos os modelos User podem acessar o Filament localmente. No entanto, ao fazer deploy para produção, você deve atualizar seu `App\Models\User.php` para implementar o contrato `FilamentUser`:

```php
<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser
{
    // ...

    public function canAccessPanel(Panel $panel): bool
    {
        return str_ends_with($this->email, '@yourdomain.com') && $this->hasVerifiedEmail();
    }
}
```

### 13.7 Usando um Disco de Armazenamento Pronto para Produção

<mcreference link="https://filamentphp.com/docs/3.x/panels/installation" index="1">1</mcreference> O Filament tem um disco de armazenamento definido na configuração, que por padrão está definido como `public`. O disco `public`, embora ótimo para desenvolvimento local fácil, não é adequado para produção. Em produção, você precisa usar um disco pronto para produção como `s3` com uma política de acesso privado.

### 13.8 Publicando Configuração

<mcreference link="https://filamentphp.com/docs/3.x/panels/installation" index="1">1</mcreference> Você pode publicar a configuração do pacote Filament (se necessário) usando o seguinte comando:

```bash
php artisan vendor:publish --tag=filament-config
```

### 13.9 Publicando Traduções

<mcreference link="https://filamentphp.com/docs/3.x/panels/installation" index="1">1</mcreference> Você pode publicar as traduções do pacote usando:

```bash
php artisan vendor:publish --tag=filament-translations
php artisan vendor:publish --tag=filament-actions-translations
php artisan vendor:publish --tag=filament-forms-translations
php artisan vendor:publish --tag=filament-infolists-translations
php artisan vendor:publish --tag=filament-notifications-translations
php artisan vendor:publish --tag=filament-tables-translations
```

### 13.10 Atualizações

<mcreference link="https://filamentphp.com/docs/3.x/panels/installation" index="1">1</mcreference> O Filament atualiza automaticamente para a versão não-breaking mais recente quando você executa `composer update`. Após quaisquer atualizações, todos os caches do Laravel precisam ser limpos, e os assets frontend precisam ser republicados:

```bash
php artisan filament:upgrade
```

## Conclusão

O Filament PHP é uma ferramenta poderosa e flexível para construir painéis administrativos e aplicações web em Laravel. Com sua arquitetura modular, API fluente e integração profunda com o ecossistema Laravel, ele permite que desenvolvedores criem interfaces sofisticadas rapidamente.

Este documento cobriu os principais aspectos do Filament, desde a instalação básica até configurações avançadas e otimizações para produção. Para informações mais detalhadas e atualizadas, sempre consulte a documentação oficial em [filamentphp.com](https://filamentphp.com).

### Recursos Adicionais

- **Documentação Oficial**: https://filamentphp.com/docs
- **Comunidade Discord**: Disponível no site oficial
- **GitHub**: https://github.com/filamentphp/filament
- **Plugins da Comunidade**: https://filamentphp.com/plugins

### Próximos Passos

1. Explore os plugins da comunidade para funcionalidades adicionais
2. Pratique criando resources personalizados
3. Experimente com widgets e dashboards
4. Implemente autenticação e autorização avançadas
5. Otimize sua aplicação para produção

O Filament continua evoluindo rapidamente, então mantenha-se atualizado com as últimas versões e recursos através da documentação oficial e da comunidade ativa.