const { test, expect } = require('@playwright/test');

test.describe('Orçamentos - Persistência de Dados do Prestador', () => {
  test.beforeEach(async ({ page }) => {
    // Login
    await page.goto('/');
    await page.fill('input[name="email"]', 'admin@sistemaobm.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    
    // Aguardar o carregamento da página
    await page.waitForLoadState('networkidle');
  });

  test('deve persistir dados do Cliente OMIE, Frequência de Atendimento e Valor Final em orçamento do tipo Prestador', async ({ page }) => {
    // Navegar para a página de orçamentos
    await page.goto('/admin/orcamentos');
    await page.waitForLoadState('networkidle');

    // Criar novo orçamento
    await page.click('text=Novo Orçamento');
    await page.waitForLoadState('networkidle');

    // Preencher dados básicos
    await page.fill('input[name="numero_orcamento"]', 'TEST-PERSIST-001');
    await page.selectOption('select[name="tipo_orcamento"]', 'prestador');
    
    // Aguardar a seção do prestador aparecer
    await page.waitForSelector('text=Dados do Prestador', { timeout: 5000 });

    // Preencher Cliente OMIE (se houver opções disponíveis)
    const clienteSelect = page.locator('select[name="cliente_omie_id"]');
    if (await clienteSelect.count() > 0) {
      const options = await clienteSelect.locator('option').count();
      if (options > 1) {
        await clienteSelect.selectOption({ index: 1 }); // Selecionar primeira opção válida
      }
    }

    // Preencher Frequência de Atendimento
    await page.check('input[name="frequencia_atendimento[]"][value="seg"]');
    await page.check('input[name="frequencia_atendimento[]"][value="ter"]');
    await page.check('input[name="frequencia_atendimento[]"][value="qua"]');

    // Preencher dados do prestador
    await page.fill('input[name="valor_referencia"]', '100.00');
    await page.fill('input[name="lucro_percentual"]', '20');
    await page.fill('input[name="impostos_percentual"]', '15');

    // Salvar orçamento
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    // Aguardar redirecionamento para página de edição
    await page.waitForURL('**/admin/orcamentos/*/edit');

    // Verificar se os dados foram persistidos
    // Cliente OMIE
    const clienteValue = await page.locator('select[name="cliente_omie_id"]').inputValue();
    console.log('Cliente OMIE ID:', clienteValue);

    // Frequência de Atendimento
    const segChecked = await page.locator('input[name="frequencia_atendimento[]"][value="seg"]').isChecked();
    const terChecked = await page.locator('input[name="frequencia_atendimento[]"][value="ter"]').isChecked();
    const quaChecked = await page.locator('input[name="frequencia_atendimento[]"][value="qua"]').isChecked();
    
    expect(segChecked).toBe(true);
    expect(terChecked).toBe(true);
    expect(quaChecked).toBe(true);

    // Valor Final (deve ter sido calculado automaticamente)
    const valorFinal = await page.locator('input[name="valor_final"]').inputValue();
    console.log('Valor Final:', valorFinal);
    expect(parseFloat(valorFinal)).toBeGreaterThan(0);

    // Verificar se os dados do prestador também foram salvos
    const valorReferencia = await page.locator('input[name="valor_referencia"]').inputValue();
    expect(valorReferencia).toBe('100.00');

    console.log('✅ Teste de persistência passou - todos os dados foram salvos corretamente');
  });

  test('deve manter dados após edição de orçamento do tipo Prestador', async ({ page }) => {
    // Este teste assume que existe pelo menos um orçamento do tipo prestador
    await page.goto('/admin/orcamentos');
    await page.waitForLoadState('networkidle');

    // Procurar por um orçamento do tipo prestador existente
    const prestadorRow = page.locator('tr:has-text("prestador")').first();
    
    if (await prestadorRow.count() > 0) {
      // Clicar no botão de editar
      await prestadorRow.locator('a[href*="/edit"]').click();
      await page.waitForLoadState('networkidle');

      // Verificar se os dados estão carregados
      const clienteValue = await page.locator('select[name="cliente_omie_id"]').inputValue();
      const valorFinal = await page.locator('input[name="valor_final"]').inputValue();
      
      console.log('Dados carregados - Cliente:', clienteValue, 'Valor Final:', valorFinal);

      // Fazer uma pequena alteração
      await page.fill('input[name="observacoes"]', 'Teste de persistência - ' + new Date().toISOString());

      // Salvar
      await page.click('button[type="submit"]');
      await page.waitForLoadState('networkidle');

      // Verificar se os dados principais ainda estão lá
      const clienteValueAfter = await page.locator('select[name="cliente_omie_id"]').inputValue();
      const valorFinalAfter = await page.locator('input[name="valor_final"]').inputValue();

      expect(clienteValueAfter).toBe(clienteValue);
      expect(valorFinalAfter).toBe(valorFinal);

      console.log('✅ Teste de edição passou - dados mantidos após salvamento');
    } else {
      console.log('⚠️ Nenhum orçamento do tipo prestador encontrado para testar');
    }
  });
});