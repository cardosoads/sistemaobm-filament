import { test, expect } from '@playwright/test';

test.describe('Orçamentos Interface', () => {
  test.beforeEach(async ({ page }) => {
    // Navegar para a página de login
    await page.goto('/admin/login');
    
    // Fazer login (assumindo que existe um usuário de teste)
    // Você pode ajustar estes seletores conforme sua interface de login
    await page.fill('input[name="email"]', 'admin@sistemaobm.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    
    // Aguardar redirecionamento após login
    await page.waitForURL('/admin');
  });

  test('deve acessar a página de orçamentos', async ({ page }) => {
    // Navegar para a página de orçamentos
    await page.goto('/admin/orcamentos');
    
    // Verificar se a página carregou corretamente
    await expect(page).toHaveTitle(/Orçamentos/);
    
    // Verificar se existe a tabela de orçamentos
    await expect(page.locator('table')).toBeVisible();
  });

  test('deve verificar que a seção Prestadores foi removida', async ({ page }) => {
    // Navegar para a página de orçamentos
    await page.goto('/admin/orcamentos');
    
    // Aguardar a página carregar
    await page.waitForLoadState('networkidle');
    
    // Procurar por um orçamento existente para editar
    const firstRow = page.locator('table tbody tr').first();
    await expect(firstRow).toBeVisible();
    
    // Clicar no primeiro orçamento para editá-lo
    await firstRow.click();
    
    // Aguardar a página de edição carregar
    await page.waitForLoadState('networkidle');
    
    // Verificar que NÃO existe a seção "Prestadores"
    await expect(page.locator('text=Prestadores')).not.toBeVisible();
    
    // Verificar que NÃO existe o tab "Prestadores"
    await expect(page.locator('[role="tab"]:has-text("Prestadores")')).not.toBeVisible();
    
    // Verificar que NÃO existe o relation manager de prestadores
    await expect(page.locator('[data-testid="prestadores-relation-manager"]')).not.toBeVisible();
  });

  test('deve verificar orçamento do tipo prestador sem seção Prestadores', async ({ page }) => {
    // Navegar para criar novo orçamento
    await page.goto('/admin/orcamentos/create');
    
    // Aguardar a página carregar
    await page.waitForLoadState('networkidle');
    
    // Selecionar tipo de orçamento como "prestador"
    await page.selectOption('select[name="tipo_orcamento"]', 'prestador');
    
    // Preencher campos obrigatórios básicos
    await page.fill('input[name="nome_cliente"]', 'Cliente Teste');
    await page.fill('input[name="descricao"]', 'Teste de orçamento prestador');
    
    // Salvar o orçamento
    await page.click('button[type="submit"]');
    
    // Aguardar redirecionamento para página de edição
    await page.waitForLoadState('networkidle');
    
    // Verificar que mesmo sendo tipo "prestador", NÃO existe a seção "Prestadores"
    await expect(page.locator('text=Prestadores')).not.toBeVisible();
    await expect(page.locator('[role="tab"]:has-text("Prestadores")')).not.toBeVisible();
  });

  test('deve verificar que outras seções ainda existem', async ({ page }) => {
    // Navegar para a página de orçamentos
    await page.goto('/admin/orcamentos');
    
    // Aguardar a página carregar
    await page.waitForLoadState('networkidle');
    
    // Procurar por um orçamento existente para editar
    const firstRow = page.locator('table tbody tr').first();
    await firstRow.click();
    
    // Aguardar a página de edição carregar
    await page.waitForLoadState('networkidle');
    
    // Verificar que outras seções ainda existem (se aplicável)
    // Ajuste conforme as seções que devem permanecer
    const possibleSections = [
      'Aumentos Km',
      'Próprios Nova Rota',
      'Detalhes',
      'Informações'
    ];
    
    // Verificar se pelo menos uma das seções esperadas existe
    let foundSection = false;
    for (const section of possibleSections) {
      const sectionElement = page.locator(`text=${section}`);
      if (await sectionElement.isVisible()) {
        foundSection = true;
        break;
      }
    }
    
    expect(foundSection).toBe(true);
  });
});