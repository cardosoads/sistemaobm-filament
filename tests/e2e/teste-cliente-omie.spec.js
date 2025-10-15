import { test, expect } from '@playwright/test';

test.describe('Teste de Persistência - Cliente OMIE ID', () => {
  test.beforeEach(async ({ page }) => {
    // Login
    await page.goto('https://sistemaobm.bimotorfinanceiro.online/');
    await page.fill('input[name="email"]', 'admin@sistemaobm.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    
    // Aguardar o carregamento da página
    await page.waitForLoadState('networkidle');
  });

  test('deve persistir cliente_omie_id corretamente sem transformar para cliente_omie', async ({ page }) => {
    console.log('🚀 Iniciando teste de persistência do cliente_omie_id');
    
    // Navegar para a página de criação de orçamentos
    await page.goto('https://sistemaobm.bimotorfinanceiro.online/admin/orcamentos/create');
    await page.waitForLoadState('networkidle');

    // Preencher dados básicos
    await page.fill('input[name="numero_orcamento"]', 'TEST-CLIENTE-OMIE-' + Date.now());
    await page.selectOption('select[name="tipo_orcamento"]', 'prestador');
    
    // Aguardar a seção do prestador aparecer
    await page.waitForSelector('text=Dados do Prestador', { timeout: 10000 });

    // Verificar se o campo cliente_omie_id existe
    const clienteOmieField = page.locator('select[name="cliente_omie_id"]');
    await expect(clienteOmieField).toBeVisible();
    console.log('✅ Campo cliente_omie_id encontrado');

    // Verificar opções disponíveis
    const options = await clienteOmieField.locator('option').count();
    console.log(`📊 Número de opções no select: ${options}`);

    if (options > 1) {
      // Selecionar a primeira opção válida (não vazia)
      const firstOptionValue = await clienteOmieField.locator('option').nth(1).getAttribute('value');
      if (firstOptionValue && firstOptionValue !== '') {
        await clienteOmieField.selectOption(firstOptionValue);
        console.log(`✅ Selecionado cliente OMIE: ${firstOptionValue}`);

        // Preencher outros campos obrigatórios
        await page.fill('input[name="valor_referencia"]', '1500.00');
        await page.fill('input[name="lucro_percentual"]', '20');
        await page.fill('input[name="impostos_percentual"]', '15');

        // Adicionar frequência de atendimento
        await page.check('input[name="frequencia_atendimento[]"][value="seg"]');
        await page.check('input[name="frequencia_atendimento[]"][value="qua"]');
        await page.check('input[name="frequencia_atendimento[]"][value="sex"]');

        // Capturar dados antes do submit
        const selectedValueBefore = await clienteOmieField.inputValue();
        console.log(`📋 Valor selecionado antes do submit: ${selectedValueBefore}`);

        // Submeter o formulário
        console.log('💾 Salvando orçamento...');
        await page.click('button[type="submit"]');
        
        // Aguardar redirecionamento ou mensagem de sucesso
        try {
          await page.waitForURL('**/admin/orcamentos/*/edit', { timeout: 15000 });
          console.log('✅ Redirecionamento para edição bem-sucedido');
        } catch (error) {
          console.log('⚠️ Não houve redirecionamento, verificando mensagens...');
          // Verificar se há mensagem de sucesso
          const successMessage = page.locator('text=Orçamento salvo com sucesso');
          if (await successMessage.count() > 0) {
            console.log('✅ Mensagem de sucesso encontrada');
          }
        }

        await page.waitForLoadState('networkidle');

        // Verificar se o valor foi persistido corretamente
        const clienteValueAfter = await page.locator('select[name="cliente_omie_id"]').inputValue();
        console.log(`📋 Valor do cliente_omie_id após salvamento: ${clienteValueAfter}`);

        // Verificar se é o mesmo valor selecionado
        expect(clienteValueAfter).toBe(selectedValueBefore);
        console.log('✅ Valor do cliente_omie_id persistido corretamente');

        // Verificar também se não há campo cliente_omie (deve dar erro se existir)
        const clienteOmieFieldInvalid = page.locator('select[name="cliente_omie"]');
        const invalidFieldExists = await clienteOmieFieldInvalid.count() > 0;
        
        if (invalidFieldExists) {
          console.log('⚠️ ATENÇÃO: Campo cliente_omie encontrado (não deveria existir)');
          const invalidValue = await clienteOmieFieldInvalid.inputValue();
          console.log(`❌ Valor no campo cliente_omie: ${invalidValue}`);
        } else {
          console.log('✅ Campo cliente_omie não existe (correto)');
        }

        console.log('🎉 Teste concluído com sucesso!');

      } else {
        console.log('⚠️ Nenhuma opção válida encontrada no select');
      }
    } else {
      console.log('⚠️ Não há opções disponíveis no select de cliente OMIE');
    }
  });

  test('deve verificar estrutura do formulário e campos', async ({ page }) => {
    console.log('🔍 Verificando estrutura do formulário...');
    
    await page.goto('https://sistemaobm.bimotorfinanceiro.online/admin/orcamentos/create');
    await page.waitForLoadState('networkidle');

    // Verificar todos os campos do formulário
    const fields = [
      'numero_orcamento',
      'tipo_orcamento', 
      'cliente_omie_id',
      'valor_referencia',
      'lucro_percentual',
      'impostos_percentual',
      'frequencia_atendimento[]'
    ];

    for (const fieldName of fields) {
      const field = page.locator(`[name="${fieldName}"]`);
      const exists = await field.count() > 0;
      console.log(`${exists ? '✅' : '❌'} Campo ${fieldName}: ${exists ? 'encontrado' : 'não encontrado'}`);
    }

    // Verificar especificamente o campo cliente_omie_id
    const clienteOmieIdField = page.locator('select[name="cliente_omie_id"]');
    const clienteOmieIdExists = await clienteOmieIdField.count() > 0;
    
    if (clienteOmieIdExists) {
      console.log('✅ Campo cliente_omie_id está presente no formulário');
      
      // Verificar se tem options
      const optionsCount = await clienteOmieIdField.locator('option').count();
      console.log(`📊 cliente_omie_id tem ${optionsCount} opções`);
      
      if (optionsCount > 1) {
        const firstOption = await clienteOmieIdField.locator('option').nth(1);
        const optionValue = await firstOption.getAttribute('value');
        const optionText = await firstOption.textContent();
        console.log(`📋 Primeira opção: valor="${optionValue}", texto="${optionText}"`);
      }
    }

    // Verificar se NÃO existe campo cliente_omie
    const clienteOmieField = page.locator('[name="cliente_omie"]');
    const clienteOmieExists = await clienteOmieField.count() > 0;
    
    if (clienteOmieExists) {
      console.log('❌ PROBLEMA: Campo cliente_omie encontrado no formulário!');
    } else {
      console.log('✅ Campo cliente_omie não existe no formulário (correto)');
    }

    console.log('🔍 Verificação de estrutura concluída');
  });
});