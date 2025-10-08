# Comparação de Migrations - Sistema OBM

## Introdução

Este documento apresenta uma comparação detalhada entre as migrations presentes em dois diretórios do projeto Sistema OBM:

- **Diretório Principal**: `/c:/Users/cardo/Herd/sistemaobm-filament/database/migrations/`
- **Diretório OBM Sistema**: `/c:/Users/cardo/Herd/sistemaobm-filament/obmsistem/database/migrations/`

A comparação foca apenas nas migrations que existem em ambos os diretórios, analisando suas diferenças estruturais e funcionais.

---

## 1. Migrations Idênticas

### 1.1 Migrations Básicas do Laravel

#### `0001_01_01_000000_create_users_table.php`
- **Status**: ✅ **Idênticas**
- **Descrição**: Migrations padrão do Laravel para usuários, tokens de reset de senha e sessões
- **Estrutura**: Completamente idêntica em ambos os diretórios

#### `0001_01_01_000001_create_cache_table.php`
- **Status**: ✅ **Idênticas**
- **Descrição**: Migration padrão do Laravel para cache

#### `0001_01_01_000002_create_jobs_table.php`
- **Status**: ✅ **Idênticas**
- **Descrição**: Migration padrão do Laravel para filas de jobs

---

## 2. Migrations com Diferenças Significativas

### 2.1 Tabela `orcamentos`

| Aspecto | Diretório Principal | Diretório OBM Sistema |
|---------|-------------------|---------------------|
| **Arquivo** | `2025_01_03_100001_create_orcamentos_table.php` | `2025_08_22_081248_create_orcamentos_table.php` |
| **Campos Únicos** | `observacoes` (text) | - |
| **Diferenças de Tipo** | `horario` (string, 50) | `horario` (time) |
| | `nome_rota` (string, 200, nullable) | `nome_rota` (string, não nullable) |
| | `numero_orcamento` (string, 50) | `numero_orcamento` (string, sem limite) |
| | `data_orcamento` (date, nullable) | `data_orcamento` (date, não nullable) |
| **Status Enum** | `['pendente', 'aprovado', 'rejeitado', 'cancelado']` | `['em_andamento', 'enviado', 'aprovado', 'rejeitado', 'cancelado']` |
| **Default Status** | `'pendente'` | `'em_andamento'` |
| **Foreign Keys** | `constrained()` direto | Definição manual com `foreign()` |
| **Índices** | Índices simples individuais | Índices compostos para performance |

### 2.2 Tabela `recursos_humanos`

| Aspecto | Diretório Principal | Diretório OBM Sistema |
|---------|-------------------|---------------------|
| **Arquivo** | `2025_10_02_000001_create_recursos_humanos_table.php` | `2025_09_10_163111_create_recursos_humanos_table.php` |
| **Estrutura** | ✅ **Idêntica** | ✅ **Idêntica** |
| **Diferença Principal** | Usa `class CreateRecursosHumanosTable extends Migration` | Usa `return new class extends Migration` |

**Campos Comuns:**
- `tipo_contratacao`, `cargo`, `base_id`
- Campos salariais: `base_salarial`, `salario_base`, `insalubridade`, `periculosidade`, etc.
- Benefícios: `vale_transporte`, `beneficios`
- Encargos: `encargos_sociais`, `custo_total_mao_obra`
- Percentuais: `percentual_encargos`, `percentual_beneficios`
- Status: `active` (boolean)

### 2.3 Tabela `bases`

| Aspecto | Diretório Principal | Diretório OBM Sistema |
|---------|-------------------|---------------------|
| **Arquivo** | `2025_10_01_060014_create_bases_table.php` | `2025_08_17_185541_create_bases_table.php` |
| **Campos Únicos Principal** | `base` (string) | `name` (string, 255) |
| | `status` (boolean, default true) | `supervisor` (string, 255) |
| | | `active` (adicionado em migration separada) |
| **Campos Comuns** | `uf` (string, 2) | `uf` (string, 2) |
| | `regional` (string) | `regional` (string, 100) |
| | `sigla` (string, 10) | `sigla` (string, 3) |

### 2.4 Tabela `centros_custo`

| Aspecto | Diretório Principal | Diretório OBM Sistema |
|---------|-------------------|---------------------|
| **Arquivo** | `2025_10_03_062846_create_centros_custo_table.php` | `2025_08_17_185731_create_centros_custo_table.php` |
| **Complexidade** | **Muito mais complexa** | **Simples** |
| **Campos Principal** | Integração completa com Omie | Estrutura básica |
| | `codigo_departamento_omie` | `name`, `codigo`, `description` |
| | `codigo_departamento_integracao` | `active` (boolean) |
| | `nome`, `descricao` | |
| | `inativo` (string, N/S) | |
| | Campos de sincronização API | |
| | Campos de auditoria | |
| **Índices** | 6 índices para performance | Nenhum índice específico |

---

## 3. Migrations Exclusivas

### 3.1 Apenas no Diretório Principal
- `2025_10_03_004125_create_frotas_table.php` - Criação da tabela frotas
- `2025_10_03_010247_add_percentual_aluguel_to_frotas_table.php` - Adição de campo percentual
- `2025_10_03_011112_remove_percentual_fipe_from_frotas_table.php` - Remoção de campo
- `2025_10_02_000001_create_recursos_humanos_table.php` - Versão mais recente
- `2025_10_03_002125_create_combustivels_table.php` - Tabela de combustíveis

### 3.2 Apenas no Diretório OBM Sistema
- Não há migration `create_frotas_table` - tabela frotas não é criada
- `2025_09_11_000001_add_percentual_provisoes_fields_to_frotas_table.php` - Adiciona campos à frotas (que não existe)
- `2025_08_17_203955_add_active_column_to_bases_table.php` - Adiciona campo active
- `2025_08_18_024013_add_fields_to_centros_custo_table.php` - Adiciona campos extras
- `2025_08_28_073540_add_omie_fields_to_centros_custo_table.php` - Campos de integração Omie

---

## 4. Análise de Inconsistências

### 4.1 Problemas Identificados

1. **Tabela Frotas**:
   - ❌ OBM Sistema não possui migration de criação da tabela `frotas`
   - ❌ Possui migrations que tentam modificar tabela inexistente

2. **Versionamento**:
   - ❌ Timestamps das migrations são inconsistentes entre diretórios
   - ❌ Algumas migrations do Principal são mais recentes que do OBM Sistema

3. **Estrutura de Dados**:
   - ⚠️ Campos obrigatórios vs opcionais diferem entre versões
   - ⚠️ Tipos de dados inconsistentes (string vs time para horário)
   - ⚠️ Enums com valores diferentes para status

### 4.2 Impactos Potenciais

1. **Incompatibilidade de Dados**: As diferenças nos tipos de campos podem causar erros na migração de dados
2. **Funcionalidades Quebradas**: Referências a campos inexistentes (como `frotas` no OBM Sistema)
3. **Inconsistência de Status**: Diferentes valores de enum podem quebrar a lógica de negócio

---

## 5. Recomendações

### 5.1 Ações Imediatas

1. **Sincronizar Migrations de Frotas**:
   - Copiar `create_frotas_table.php` do Principal para OBM Sistema
   - Revisar migrations de modificação da tabela frotas

2. **Padronizar Estrutura de Orçamentos**:
   - Decidir qual versão da tabela `orcamentos` será a definitiva
   - Criar migration de migração de dados se necessário

3. **Unificar Centros de Custo**:
   - Avaliar se a integração com Omie é necessária em ambos os sistemas
   - Criar estratégia de migração dos dados existentes

### 5.2 Ações de Longo Prazo

1. **Versionamento Único**: Estabelecer um único diretório de migrations como fonte da verdade
2. **Testes de Migração**: Implementar testes automatizados para validar migrations
3. **Documentação**: Manter documentação atualizada das diferenças estruturais

---

## 6. Conclusão

A análise revela diferenças significativas entre os dois conjuntos de migrations, com o **Diretório Principal** apresentando uma estrutura mais completa e recente, especialmente para integração com sistemas externos (Omie). O **Diretório OBM Sistema** possui algumas inconsistências críticas, como a ausência da tabela `frotas` e migrations que tentam modificá-la.

**Recomendação Principal**: Utilizar o Diretório Principal como base e migrar cuidadosamente os dados do OBM Sistema, resolvendo as inconsistências identificadas antes da unificação.