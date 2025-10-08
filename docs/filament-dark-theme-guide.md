# Guia de Tema Escuro - Elementos DIV no Filament

## Composição Padrão de Tema Escuro

### 1. Containers Principais (Cards/Widgets)
```html
<!-- Container principal com sombra e borda -->
<div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
    <!-- Conteúdo -->
</div>
```

### 2. Seções de Conteúdo
```html
<!-- Seção de destaque/informação -->
<div class="bg-gray-50 dark:bg-white/5 p-4 rounded-lg">
    <!-- Conteúdo -->
</div>

<!-- Container de informação com borda -->
<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
    <!-- Conteúdo -->
</div>
```

### 3. Estados de Feedback

#### Estado de Erro
```html
<div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
    <div class="text-red-800 dark:text-red-200">Mensagem de erro</div>
</div>
```

#### Estado de Sucesso
```html
<div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
    <div class="text-green-800 dark:text-green-200">Mensagem de sucesso</div>
</div>
```

#### Estado de Aviso
```html
<div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
    <div class="text-yellow-800 dark:text-yellow-200">Mensagem de aviso</div>
</div>
```

#### Estado de Informação
```html
<div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
    <div class="text-blue-800 dark:text-blue-200">Mensagem informativa</div>
</div>
```

### 4. Elementos de Texto

#### Títulos e Cabeçalhos
```html
<div class="text-gray-950 dark:text-white font-semibold">Título Principal</div>
<div class="text-gray-700 dark:text-gray-200 font-medium">Subtítulo</div>
```

#### Texto Secundário
```html
<div class="text-gray-600 dark:text-gray-400">Texto secundário</div>
<div class="text-gray-500 dark:text-gray-400">Texto auxiliar</div>
```

#### Texto Desabilitado
```html
<div class="text-gray-400 dark:text-gray-500">Texto desabilitado</div>
```

### 5. Separadores e Divisores
```html
<!-- Divisor horizontal -->
<div class="border-t border-gray-200 dark:border-white/10"></div>

<!-- Divisor vertical -->
<div class="border-l border-gray-200 dark:border-white/10"></div>
```

### 6. Containers de Lista/Grid
```html
<!-- Container de lista com divisores -->
<div class="divide-y divide-gray-200 dark:divide-white/10">
    <div class="py-4">Item 1</div>
    <div class="py-4">Item 2</div>
</div>
```

### 7. Overlays e Modais
```html
<!-- Overlay de fundo -->
<div class="fixed inset-0 bg-gray-950/50 dark:bg-gray-950/75"></div>

<!-- Container do modal -->
<div class="bg-white dark:bg-gray-900 rounded-xl shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10">
    <!-- Conteúdo do modal -->
</div>
```

## Padrões de Cores por Contexto

### Backgrounds
- **Primário**: `bg-white dark:bg-gray-900`
- **Secundário**: `bg-gray-50 dark:bg-white/5`
- **Terciário**: `bg-gray-100 dark:bg-white/10`
- **Card/Container**: `bg-white dark:bg-gray-800`

### Bordas
- **Padrão**: `border-gray-200 dark:border-white/10`
- **Sutil**: `border-gray-100 dark:border-white/5`
- **Destaque**: `border-gray-300 dark:border-gray-600`

### Texto
- **Primário**: `text-gray-950 dark:text-white`
- **Secundário**: `text-gray-700 dark:text-gray-200`
- **Terciário**: `text-gray-600 dark:text-gray-400`
- **Auxiliar**: `text-gray-500 dark:text-gray-400`
- **Desabilitado**: `text-gray-400 dark:text-gray-500`

## Exemplo Prático de Aplicação

```html
<!-- Widget de informação seguindo padrões do Filament -->
<div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6">
    <!-- Cabeçalho -->
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-950 dark:text-white">
            Título do Widget
        </h3>
        <div class="text-sm text-gray-500 dark:text-gray-400">
            Informação adicional
        </div>
    </div>
    
    <!-- Conteúdo principal -->
    <div class="bg-gray-50 dark:bg-white/5 rounded-lg p-4 mb-4">
        <div class="text-gray-700 dark:text-gray-200">
            Conteúdo principal do widget
        </div>
    </div>
    
    <!-- Rodapé com ações -->
    <div class="border-t border-gray-200 dark:border-white/10 pt-4">
        <div class="flex justify-end gap-2">
            <!-- Botões seguindo padrões do Filament -->
        </div>
    </div>
</div>
```

## Notas Importantes

1. **Consistência**: Sempre use os padrões estabelecidos pelo Filament
2. **Opacidade**: Use `/5`, `/10`, `/20` para transparências no tema escuro
3. **Contraste**: Garanta contraste adequado entre texto e fundo
4. **Hierarquia**: Mantenha hierarquia visual clara com diferentes tons de cinza
5. **Estados**: Use cores semânticas para estados (erro, sucesso, aviso, info)