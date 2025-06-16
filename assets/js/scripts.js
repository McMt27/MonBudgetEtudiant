
/**
 * MonBudgetÉtudiant - Script principal
 * Gestion du budget étudiant avec différents scénarios
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des graphiques si la page contient des éléments canvas
    initCharts();
    
    // Initialisation du mode (alternance ou non)
    initModeToggle();
    
    // Gestionnaires d'événements pour les formulaires
    initFormHandlers();
    
    // Initialisation de la simulation APL
    initAplSimulation();
    
    // Vérification du budget (alerte si négatif)
    checkBudgetStatus();
});

/**
 * Initialise les graphiques de visualisation du budget
 */
function initCharts() {
    // Vérifier si les éléments canvas existent sur la page
    const pieChartCanvas = document.getElementById('expensesPieChart');
    const barChartCanvas = document.getElementById('monthlyBarChart');
    
    if (pieChartCanvas) {
        initPieChart(pieChartCanvas);
    }
    
    if (barChartCanvas) {
        initBarChart(barChartCanvas);
    }
}

/**
 * Initialise le graphique en camembert des dépenses
 * @param {HTMLElement} canvas - Élément canvas pour le graphique
 */
function initPieChart(canvas) {
    // Récupération des données (à remplacer par des données réelles)
    const expensesData = getExpensesData();
    
    // Utilisation de l'API Canvas pour créer un graphique en camembert
    const ctx = canvas.getContext('2d');
    let startAngle = 0;
    let total = 0;
    
    // Calculer le total
    expensesData.forEach(item => {
        total += item.amount;
    });
    
    // Couleurs pour le graphique
    const colors = [
        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
        '#9966FF', '#FF9F40', '#8AC249', '#EA526F'
    ];
    
    // Dessiner le camembert
    expensesData.forEach((item, index) => {
        const sliceAngle = (2 * Math.PI * item.amount) / total;
        
        ctx.fillStyle = colors[index % colors.length];
        ctx.beginPath();
        ctx.moveTo(canvas.width / 2, canvas.height / 2);
        ctx.arc(
            canvas.width / 2, 
            canvas.height / 2, 
            Math.min(canvas.width / 2, canvas.height / 2) - 10, 
            startAngle, 
            startAngle + sliceAngle
        );
        ctx.closePath();
        ctx.fill();
        
        startAngle += sliceAngle;
    });
    
    // Créer la légende
    createPieChartLegend(expensesData, colors);
}

/**
 * Crée la légende pour le graphique en camembert
 * @param {Array} data - Données des dépenses
 * @param {Array} colors - Couleurs utilisées dans le graphique
 */
function createPieChartLegend(data, colors) {
    const legendContainer = document.getElementById('pieChartLegend');
    if (!legendContainer) return;
    
    legendContainer.innerHTML = '';
    
    data.forEach((item, index) => {
        const legendItem = document.createElement('div');
        legendItem.className = 'legend-item';
        
        const colorBox = document.createElement('span');
        colorBox.className = 'color-box';
        colorBox.style.backgroundColor = colors[index % colors.length];
        
        const label = document.createElement('span');
        label.className = 'legend-label';
        label.textContent = `${item.label}: ${item.amount}€`;
        
        legendItem.appendChild(colorBox);
        legendItem.appendChild(label);
        legendContainer.appendChild(legendItem);
    });
}

/**
 * Initialise le graphique à barres des revenus/dépenses mensuels
 * @param {HTMLElement} canvas - Élément canvas pour le graphique
 */
function initBarChart(canvas) {
    const ctx = canvas.getContext('2d');
    const monthlyData = getMonthlyData();
    const months = Object.keys(monthlyData);
    
    const barWidth = (canvas.width - 100) / months.length / 2;
    const maxValue = Math.max(
        ...Object.values(monthlyData).map(m => Math.max(m.income, m.expenses))
    );
    
    // Dessiner les axes
    ctx.strokeStyle = '#000';
    ctx.beginPath();
    ctx.moveTo(50, 30);
    ctx.lineTo(50, canvas.height - 50);
    ctx.lineTo(canvas.width - 20, canvas.height - 50);
    ctx.stroke();
    
    // Étiquettes de l'axe Y
    ctx.fillStyle = '#000';
    ctx.font = '12px Arial';
    for (let i = 0; i <= 5; i++) {
        const y = canvas.height - 50 - (i * (canvas.height - 80) / 5);
        const value = Math.round(maxValue * i / 5);
        ctx.fillText(value + '€', 10, y + 5);
    }
    
    // Dessiner les barres et étiquettes
    months.forEach((month, index) => {
        const x = 70 + index * (barWidth * 2 + 20);
        const incomeHeight = (monthlyData[month].income / maxValue) * (canvas.height - 80);
        const expensesHeight = (monthlyData[month].expenses / maxValue) * (canvas.height - 80);
        
        // Barre de revenus
        ctx.fillStyle = '#36A2EB';
        ctx.fillRect(x, canvas.height - 50 - incomeHeight, barWidth, incomeHeight);
        
        // Barre de dépenses
        ctx.fillStyle = '#FF6384';
        ctx.fillRect(x + barWidth + 5, canvas.height - 50 - expensesHeight, barWidth, expensesHeight);
        
        // Étiquette du mois
        ctx.fillStyle = '#000';
        ctx.fillText(month, x + barWidth - 10, canvas.height - 30);
    });
    
    // Légende
    ctx.fillStyle = '#36A2EB';
    ctx.fillRect(canvas.width - 150, 20, 15, 15);
    ctx.fillStyle = '#000';
    ctx.fillText('Revenus', canvas.width - 130, 32);
    
    ctx.fillStyle = '#FF6384';
    ctx.fillRect(canvas.width - 150, 45, 15, 15);
    ctx.fillStyle = '#000';
    ctx.fillText('Dépenses', canvas.width - 130, 57);
}

/**
 * Récupère les données de dépenses pour le graphique
 * @returns {Array} Tableau d'objets représentant les dépenses
 */
function getExpensesData() {
    // À remplacer par des données réelles provenant du serveur/localStorage
    const storedData = localStorage.getItem('budgetExpenses');
    
    if (storedData) {
        return JSON.parse(storedData);
    }
    
    // Données par défaut
    return [
        { label: 'Loyer', amount: 450 },
        { label: 'Courses', amount: 250 },
        { label: 'Transport', amount: 60 },
        { label: 'Loisirs', amount: 80 },
        { label: 'Factures', amount: 70 }
    ];
}

/**
 * Récupère les données mensuelles pour le graphique à barres
 * @returns {Object} Objet contenant les données mensuelles
 */
function getMonthlyData() {
    // À remplacer par des données réelles provenant du serveur/localStorage
    const storedData = localStorage.getItem('monthlyBudgetData');
    
    if (storedData) {
        return JSON.parse(storedData);
    }
    
    // Données par défaut
    return {
        'Jan': { income: 1200, expenses: 950 },
        'Fév': { income: 1200, expenses: 980 },
        'Mar': { income: 1300, expenses: 1050 },
        'Avr': { income: 1250, expenses: 900 },
        'Mai': { income: 1200, expenses: 930 },
        'Juin': { income: 1350, expenses: 1000 }
    };
}

/**
 * Initialise le basculement entre les modes (avec/sans alternance)
 */
function initModeToggle() {
    const modeToggle = document.getElementById('modeToggle');
    
    if (modeToggle) {
        // Récupérer le mode actuel depuis localStorage ou définir par défaut
        const currentMode = localStorage.getItem('budgetMode') || 'withAlternance';
        modeToggle.checked = currentMode === 'withAlternance';
        
        // Appliquer le mode actuel
        applyBudgetMode(currentMode);
        
        // Gestionnaire d'événement pour le changement de mode
        modeToggle.addEventListener('change', function() {
            const newMode = this.checked ? 'withAlternance' : 'withoutAlternance';
            localStorage.setItem('budgetMode', newMode);
            applyBudgetMode(newMode);
        });
    }
}

/**
 * Applique les changements selon le mode sélectionné
 * @param {string} mode - Mode budgétaire ('withAlternance' ou 'withoutAlternance')
 */
function applyBudgetMode(mode) {
    const alternanceFields = document.querySelectorAll('.alternance-field');
    const nonAlternanceFields = document.querySelectorAll('.non-alternance-field');
    
    if (mode === 'withAlternance') {
        alternanceFields.forEach(field => field.classList.remove('hidden'));
        nonAlternanceFields.forEach(field => field.classList.add('hidden'));
    } else {
        alternanceFields.forEach(field => field.classList.add('hidden'));
        nonAlternanceFields.forEach(field => field.classList.remove('hidden'));
    }
    
    // Recalculer le budget
    calculateBudget();
}

/**
 * Initialise les gestionnaires d'événements pour les formulaires
 */
function initFormHandlers() {
    const incomeForm = document.getElementById('incomeForm');
    const expenseForm = document.getElementById('expenseForm');
    
    if (incomeForm) {
        incomeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            saveIncomeData();
        });
        
        // Charger les données existantes
        loadIncomeData();
    }
    
    if (expenseForm) {
        expenseForm.addEventListener('submit', function(e) {
            e.preventDefault();
            saveExpenseData();
        });
        
        // Charger les données existantes
        loadExpenseData();
    }
    
    // Gestionnaire pour l'ajout dynamique de champs
    const addIncomeBtn = document.getElementById('addIncomeField');
    const addExpenseBtn = document.getElementById('addExpenseField');
    
    if (addIncomeBtn) {
        addIncomeBtn.addEventListener('click', function() {
            addCustomField('incomeFields', 'income');
        });
    }
    
    if (addExpenseBtn) {
        addExpenseBtn.addEventListener('click', function() {
            addCustomField('expenseFields', 'expense');
        });
    }
}

/**
 * Ajoute un champ personnalisé aux formulaires
 * @param {string} containerId - ID du conteneur où ajouter le champ
 * @param {string} type - Type de champ ('income' ou 'expense')
 */
function addCustomField(containerId, type) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    const fieldId = `custom-${type}-${Date.now()}`;
    
    const fieldGroup = document.createElement('div');
    fieldGroup.className = 'field-group custom-field';
    fieldGroup.dataset.fieldId = fieldId;
    
    const labelInput = document.createElement('input');
    labelInput.type = 'text';
    labelInput.name = `${type}Label[]`;
    labelInput.placeholder = 'Libellé';
    labelInput.required = true;
    
    const amountInput = document.createElement('input');
    amountInput.type = 'number';
    amountInput.name = `${type}Amount[]`;
    amountInput.placeholder = 'Montant (€)';
    amountInput.step = '0.01';
    amountInput.min = '0';
    amountInput.required = true;
    
    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'remove-field';
    removeBtn.textContent = '×';
    removeBtn.addEventListener('click', function() {
        container.removeChild(fieldGroup);
        calculateBudget();
    });
    
    fieldGroup.appendChild(labelInput);
    fieldGroup.appendChild(amountInput);
    fieldGroup.appendChild(removeBtn);
    container.appendChild(fieldGroup);
    
    // Mettre à jour les écouteurs d'événements pour les nouveaux champs
    labelInput.addEventListener('change', calculateBudget);
    amountInput.addEventListener('change', calculateBudget);
}

/**
 * Enregistre les données de revenus
 */
function saveIncomeData() {
    const incomeData = {
        alternance: parseFloat(document.getElementById('alternanceIncome')?.value || 0),
        job: parseFloat(document.getElementById('jobIncome')?.value || 0),
        scholarship: parseFloat(document.getElementById('scholarshipIncome')?.value || 0),
        parentalSupport: parseFloat(document.getElementById('parentalSupportIncome')?.value || 0),
        housing: parseFloat(document.getElementById('housingIncome')?.value || 0),
        custom: []
    };
    
    // Récupérer les champs personnalisés
    const customFields = document.querySelectorAll('#incomeFields .custom-field');
    customFields.forEach(field => {
        const label = field.querySelector('input[name="incomeLabel[]"]').value;
        const amount = parseFloat(field.querySelector('input[name="incomeAmount[]"]').value);
        
        incomeData.custom.push({ label, amount });
    });
    
    // Enregistrer dans localStorage
    localStorage.setItem('budgetIncome', JSON.stringify(incomeData));
    
    // Recalculer le budget
    calculateBudget();
    
    // Afficher un message de confirmation
    showNotification('Revenus enregistrés avec succès !', 'success');
}

/**
 * Charge les données de revenus existantes
 */
function loadIncomeData() {
    const storedData = localStorage.getItem('budgetIncome');
    
    if (storedData) {
        const incomeData = JSON.parse(storedData);
        
        // Remplir les champs standard
        if (document.getElementById('alternanceIncome')) 
            document.getElementById('alternanceIncome').value = incomeData.alternance || '';
        if (document.getElementById('jobIncome'))
            document.getElementById('jobIncome').value = incomeData.job || '';
        if (document.getElementById('scholarshipIncome'))
            document.getElementById('scholarshipIncome').value = incomeData.scholarship || '';
        if (document.getElementById('parentalSupportIncome'))
            document.getElementById('parentalSupportIncome').value = incomeData.parentalSupport || '';
        if (document.getElementById('housingIncome'))
            document.getElementById('housingIncome').value = incomeData.housing || '';
        
        // Ajouter les champs personnalisés
        const incomeFields = document.getElementById('incomeFields');
        
        if (incomeFields && incomeData.custom) {
            incomeData.custom.forEach(item => {
                const fieldId = `custom-income-${Date.now()}`;
                
                const fieldGroup = document.createElement('div');
                fieldGroup.className = 'field-group custom-field';
                fieldGroup.dataset.fieldId = fieldId;
                
                const labelInput = document.createElement('input');
                labelInput.type = 'text';
                labelInput.name = `incomeLabel[]`;
                labelInput.value = item.label;
                labelInput.required = true;
                
                const amountInput = document.createElement('input');
                amountInput.type = 'number';
                amountInput.name = `incomeAmount[]`;
                amountInput.value = item.amount;
                amountInput.step = '0.01';
                amountInput.min = '0';
                amountInput.required = true;
                
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'remove-field';
                removeBtn.textContent = '×';
                removeBtn.addEventListener('click', function() {
                    incomeFields.removeChild(fieldGroup);
                    calculateBudget();
                });
                
                fieldGroup.appendChild(labelInput);
                fieldGroup.appendChild(amountInput);
                fieldGroup.appendChild(removeBtn);
                incomeFields.appendChild(fieldGroup);
                
                // Mettre à jour les écouteurs d'événements pour les nouveaux champs
                labelInput.addEventListener('change', calculateBudget);
                amountInput.addEventListener('change', calculateBudget);
            });
        }
    }
}

/**
 * Enregistre les données de dépenses
 */
function saveExpenseData() {
    const expenseData = {
        rent: parseFloat(document.getElementById('rentExpense')?.value || 0),
        groceries: parseFloat(document.getElementById('groceriesExpense')?.value || 0),
        transport: parseFloat(document.getElementById('transportExpense')?.value || 0),
        leisure: parseFloat(document.getElementById('leisureExpense')?.value || 0),
        bills: parseFloat(document.getElementById('billsExpense')?.value || 0),
        custom: []
    };
    
    // Récupérer les champs personnalisés
    const customFields = document.querySelectorAll('#expenseFields .custom-field');
    customFields.forEach(field => {
        const label = field.querySelector('input[name="expenseLabel[]"]').value;
        const amount = parseFloat(field.querySelector('input[name="expenseAmount[]"]').value);
        
        expenseData.custom.push({ label, amount });
    });
    
    // Enregistrer dans localStorage
    localStorage.setItem('budgetExpenses', JSON.stringify(expenseData));
    
    // Convertir en format utilisable par le graphique
    const chartData = [
        { label: 'Loyer', amount: expenseData.rent },
        { label: 'Courses', amount: expenseData.groceries },
        { label: 'Transport', amount: expenseData.transport },
        { label: 'Loisirs', amount: expenseData.leisure },
        { label: 'Factures', amount: expenseData.bills }
    ];
    
    // Ajouter les dépenses personnalisées
    expenseData.custom.forEach(item => {
        chartData.push({ label: item.label, amount: item.amount });
    });
    
    // Enregistrer les données pour le graphique
    localStorage.setItem('budgetExpensesChart', JSON.stringify(chartData));
    
    // Recalculer le budget
    calculateBudget();
    
    // Rafraîchir les graphiques
    initCharts();
    
    // Afficher un message de confirmation
    showNotification('Dépenses enregistrées avec succès !', 'success');
}

/**
 * Charge les données de dépenses existantes
 */
function loadExpenseData() {
    const storedData = localStorage.getItem('budgetExpenses');
    
    if (storedData) {
        const expenseData = JSON.parse(storedData);
        
        // Remplir les champs standard
        if (document.getElementById('rentExpense')) 
            document.getElementById('rentExpense').value = expenseData.rent || '';
        if (document.getElementById('groceriesExpense'))
            document.getElementById('groceriesExpense').value = expenseData.groceries || '';
        if (document.getElementById('transportExpense'))
            document.getElementById('transportExpense').value = expenseData.transport || '';
        if (document.getElementById('leisureExpense'))
            document.getElementById('leisureExpense').value = expenseData.leisure || '';
        if (document.getElementById('billsExpense'))
            document.getElementById('billsExpense').value = expenseData.bills || '';
        
        // Ajouter les champs personnalisés
        const expenseFields = document.getElementById('expenseFields');
        
        if (expenseFields && expenseData.custom) {
            expenseData.custom.forEach(item => {
                const fieldId = `custom-expense-${Date.now()}`;
                
                const fieldGroup = document.createElement('div');
                fieldGroup.className = 'field-group custom-field';
                fieldGroup.dataset.fieldId = fieldId;
                
                const labelInput = document.createElement('input');
                labelInput.type = 'text';
                labelInput.name = `expenseLabel[]`;
                labelInput.value = item.label;
                labelInput.required = true;
                
                const amountInput = document.createElement('input');
                amountInput.type = 'number';
                amountInput.name = `expenseAmount[]`;
                amountInput.value = item.amount;
                amountInput.step = '0.01';
                amountInput.min = '0';
                amountInput.required = true;
                
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'remove-field';
                removeBtn.textContent = '×';
                removeBtn.addEventListener('click', function() {
                    expenseFields.removeChild(fieldGroup);
                    calculateBudget();
                });
                
                fieldGroup.appendChild(labelInput);
                fieldGroup.appendChild(amountInput);
                fieldGroup.appendChild(removeBtn);
                expenseFields.appendChild(fieldGroup);
                
                // Mettre à jour les écouteurs d'événements pour les nouveaux champs
                labelInput.addEventListener('change', calculateBudget);
                amountInput.addEventListener('change', calculateBudget);
            });
        }
    }
}

/**
 * Calcule le budget total et met à jour l'affichage
 */
function calculateBudget() {
    let totalIncome = 0;
    let totalExpenses = 0;
    let balance = 0;
    
    // Calculer les revenus
    const incomeData = localStorage.getItem('budgetIncome');
    if (incomeData) {
        const income = JSON.parse(incomeData);
        const currentMode = localStorage.getItem('budgetMode') || 'withAlternance';
        
        if (currentMode === 'withAlternance') {
            totalIncome += income.alternance || 0;
        } else {
            totalIncome += income.job || 0;
        }
        
        totalIncome += income.scholarship || 0;
        totalIncome += income.parentalSupport || 0;
        totalIncome += income.housing || 0;
        
        // Ajouter les revenus personnalisés
        if (income.custom) {
            income.custom.forEach(item => {
                totalIncome += item.amount || 0;
            });
        }
    }
    
    // Calculer les dépenses
    const expenseData = localStorage.getItem('budgetExpenses');
    if (expenseData) {
        const expenses = JSON.parse(expenseData);
        
        totalExpenses += expenses.rent || 0;
        totalExpenses += expenses.groceries || 0;
        totalExpenses += expenses.transport || 0;
        totalExpenses += expenses.leisure || 0;
        totalExpenses += expenses.bills || 0;
        
        // Ajouter les dépenses personnalisées
        if (expenses.custom) {
            expenses.custom.forEach(item => {
                totalExpenses += item.amount || 0;
            });
        }
    }
    
    // Calculer le solde
    balance = totalIncome - totalExpenses;

    // Mettre à jour l'affichage
    const totalIncomeElement = document.getElementById('totalIncome');
    const totalExpensesElement = document.getElementById('totalExpenses');
    const balanceElement = document.getElementById('balance');
    
    if (totalIncomeElement) totalIncomeElement.textContent = totalIncome.toFixed(2) + ' €';
    if (totalExpensesElement) totalExpensesElement.textContent = totalExpenses.toFixed(2) + ' €';
    
    if (balanceElement) {
        balanceElement.textContent = balance.toFixed(2) + ' €';
        balanceElement.className = balance < 0 ? 'negative' : 'positive';
    
    checkBudgetStatus(balance);

    return { totalIncome, totalExpenses, balance };
    }
}

/**
 * Vérifie l'état du budget et affiche une alerte si négatif
 */
function checkBudgetStatus(balance) {
    const alertBanner = document.getElementById('budgetAlert');

    if (alertBanner) {
        if (balance < 0) {
            alertBanner.classList.remove('hidden');
            alertBanner.textContent = `⚠️ Attention ! Votre budget est négatif (${balance.toFixed(2)} €)`;
        } else {
            alertBanner.classList.add('hidden');
        }
    }
}


/**
 * Initialise le simulateur d'APL
 */
function initAplSimulation() {
    const aplForm = document.getElementById('aplForm');
    
    if (aplForm) {
        aplForm.addEventListener('submit', function(e) {
            e.preventDefault();
            simulateApl();
        });
    }
}

/**
 * Simule le calcul de l'APL
 */
function simulateApl() {
    const rent = parseFloat(document.getElementById('aplRent').value) || 0;
    const income = parseFloat(document.getElementById('aplIncome').value) || 0;
    const zone = document.getElementById('aplZone').value;
    const isCouple = document.getElementById('aplCouple').checked;
    
    // Estimation très simplifiée du montant d'APL
    // Formule complète : https://www.service-public.fr/particuliers/vosdroits/F12006
    let aplAmount = 0;
    
    // Loyer de référence selon la zone
    let referenceRent = 0;
    switch (zone) {
        case 'zone1':
            referenceRent = 295;
            break;
        case 'zone2':
            referenceRent = 258;
            break;
        case 'zone3':
            referenceRent = 241;
            break;
    }
    
    // Augmentation pour couple
    if (isCouple) {
        referenceRent *= 1.2;
    }
    
    // Calcul très simplifié (à titre indicatif seulement)
    if (rent > referenceRent) {
        rent = referenceRent;
    }
    
    // Coefficient dégressif selon revenus
    const incomeCoefficient = Math.max(0, 1 - (income / 15000));
    
    aplAmount = rent * 0.6 * incomeCoefficient;
    
    // Arrondir et plafonner
    aplAmount = Math.min(Math.round(aplAmount), 500);
    
    // Afficher le résultat
    const resultElement = document.getElementById('aplResult');
    if (resultElement) {
        resultElement.textContent = `Estimation APL : ${aplAmount.toFixed(2)} € / mois`;
        resultElement.classList.remove('hidden');
    }
    
    // Ajouter un lien vers la CAF
    const cafLinkElement = document.getElementById('cafLink');
    if (cafLinkElement) {
        cafLinkElement.classList.remove('hidden');
    }
}

/**
 * Affiche une notification
 * @param {string} message - Message à afficher
 * @param {string} type - Type de notification ('success', 'error', 'warning')
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Animation d'entrée
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateY(0)';
    }, 10);
    
    // Suppression automatique
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
        
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

/**
 * Fonction d'export en PDF
 */
function exportToPdf() {
    showNotification('Génération du PDF en cours...', 'info');
    
    // Simuler un délai de génération
    setTimeout(() => {
        // Ici, on simulerait la génération d'un PDF
        // En production, il faudrait utiliser une bibliothèque comme jsPDF ou
        // envoyer les données au serveur pour générer le PDF avec PHP
        
        showNotification('Téléchargement du PDF démarré !', 'success');
        
        // Simuler un téléchargement
        const link = document.createElement('a');
        link.href = '#';
        link.download = 'MonBudgetEtudiant.pdf';
        link.click();
    }, 1500);
}

/**
 * Fonction d'export en Excel
 */
function exportToExcel() {
    showNotification('Génération du fichier Excel en cours...', 'info');
    
    // Simuler un délai de génération
    setTimeout(() => {
        // En production, utiliser une bibliothèque comme SheetJS (xlsx)
        // ou envoyer les données au serveur pour générer l'Excel
        
        const { totalIncome, totalExpenses, balance } = calculateBudget();
        
        // Récupérer les données de revenus et dépenses
        const incomeData = JSON.parse(localStorage.getItem('budgetIncome') || '{}');
        const expenseData = JSON.parse(localStorage.getItem('budgetExpenses') || '{}');
        
        // Ici simulons juste un téléchargement
        showNotification('Téléchargement du fichier Excel démarré !', 'success');
        
        const link = document.createElement('a');
        link.href = '#';
        link.download = 'MonBudgetEtudiant.xlsx';
        link.click();
    }, 1500);
}

/**
 * Crée une sauvegarde des données budgétaires
 */
function createBackup() {
    try {
        const backupData = {
            income: JSON.parse(localStorage.getItem('budgetIncome') || '{}'),
            expenses: JSON.parse(localStorage.getItem('budgetExpenses') || '{}'),
            monthlyData: JSON.parse(localStorage.getItem('monthlyBudgetData') || '{}'),
            mode: localStorage.getItem('budgetMode') || 'withAlternance',
            timestamp: new Date().toISOString()
        };
        
        const backupString = JSON.stringify(backupData);
        const backupBlob = new Blob([backupString], { type: 'application/json' });
        const backupUrl = URL.createObjectURL(backupBlob);
        
        const link = document.createElement('a');
        link.href = backupUrl;
        link.download = `MonBudgetEtudiant_Backup_${new Date().toISOString().slice(0, 10)}.json`;
        link.click();
        
        URL.revokeObjectURL(backupUrl);
        
        showNotification('Sauvegarde créée avec succès !', 'success');
    } catch (error) {
        showNotification('Erreur lors de la création de la sauvegarde.', 'error');
        console.error('Erreur de sauvegarde:', error);
    }
}

/**
 * Restaure une sauvegarde des données budgétaires
 * @param {File} file - Fichier de sauvegarde à restaurer
 */
function restoreBackup(file) {
    const reader = new FileReader();
    
    reader.onload = function(event) {
        try {
            const backupData = JSON.parse(event.target.result);
            
            // Vérifier la validité des données
            if (!backupData.income || !backupData.expenses) {
                throw new Error('Fichier de sauvegarde invalide.');
            }
            
            // Restaurer les données
            localStorage.setItem('budgetIncome', JSON.stringify(backupData.income));
            localStorage.setItem('budgetExpenses', JSON.stringify(backupData.expenses));
            
            if (backupData.monthlyData) {
                localStorage.setItem('monthlyBudgetData', JSON.stringify(backupData.monthlyData));
            }
            
            if (backupData.mode) {
                localStorage.setItem('budgetMode', backupData.mode);
            }
            
            // Recharger les données dans l'interface
            loadIncomeData();
            loadExpenseData();
            
            // Appliquer le mode
            const modeToggle = document.getElementById('modeToggle');
            if (modeToggle) {
                modeToggle.checked = backupData.mode === 'withAlternance';
                applyBudgetMode(backupData.mode);
            }
            
            // Rafraîchir les graphiques
            initCharts();
            
            showNotification('Sauvegarde restaurée avec succès !', 'success');
        } catch (error) {
            showNotification('Erreur lors de la restauration: fichier invalide.', 'error');
            console.error('Erreur de restauration:', error);
        }
    };
    
    reader.onerror = function() {
        showNotification('Erreur lors de la lecture du fichier.', 'error');
    };
    
    reader.readAsText(file);
}

/**
 * Initialise la fonctionnalité de sauvegarde/restauration
 */
function initBackupRestore() {
    const backupBtn = document.getElementById('createBackupBtn');
    const restoreInput = document.getElementById('restoreBackupInput');
    const restoreBtn = document.getElementById('restoreBackupBtn');
    
    if (backupBtn) {
        backupBtn.addEventListener('click', createBackup);
    }
    
    if (restoreBtn && restoreInput) {
        restoreBtn.addEventListener('click', function() {
            restoreInput.click();
        });
        
        restoreInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                restoreBackup(this.files[0]);
            }
        });
    }
}

/**
 * Génère des statistiques basées sur les données budgétaires
 */
function generateStats() {
    const statsContainer = document.getElementById('budgetStats');
    if (!statsContainer) return;
    
    // Calculer les totaux
    const { totalIncome, totalExpenses, balance } = calculateBudget();
    
    // Récupérer les données mensuelles
    const monthlyData = getMonthlyData();
    const months = Object.keys(monthlyData);
    
    // Calculer les tendances
    let incomeTrend = 0;
    let expenseTrend = 0;
    
    if (months.length > 1) {
        const firstMonth = monthlyData[months[0]];
        const lastMonth = monthlyData[months[months.length - 1]];
        
        incomeTrend = ((lastMonth.income - firstMonth.income) / firstMonth.income) * 100;
        expenseTrend = ((lastMonth.expenses - firstMonth.expenses) / firstMonth.expenses) * 100;
    }
    
    // Calculer le taux d'épargne
    const savingsRate = balance > 0 ? (balance / totalIncome) * 100 : 0;
    
    // Calculer la répartition des dépenses
    const expenseData = JSON.parse(localStorage.getItem('budgetExpenses') || '{}');
    let rentPercentage = 0;
    
    if (expenseData.rent && totalExpenses > 0) {
        rentPercentage = (expenseData.rent / totalExpenses) * 100;
    }
    
    // Créer l'affichage des statistiques
    statsContainer.innerHTML = '';
    
    const statItems = [
        {
            label: 'Taux d\'épargne',
            value: `${savingsRate.toFixed(1)}%`,
            class: savingsRate >= 10 ? 'positive' : savingsRate > 0 ? 'neutral' : 'negative',
            help: 'Un bon taux d\'épargne est généralement supérieur à 10% des revenus.'
        },
        {
            label: 'Part du loyer dans les dépenses',
            value: `${rentPercentage.toFixed(1)}%`,
            class: rentPercentage <= 33 ? 'positive' : rentPercentage <= 50 ? 'neutral' : 'negative',
            help: 'Le loyer ne devrait idéalement pas dépasser 33% de vos dépenses totales.'
        },
        {
            label: 'Tendance des revenus',
            value: `${incomeTrend > 0 ? '+' : ''}${incomeTrend.toFixed(1)}%`,
            class: incomeTrend > 0 ? 'positive' : incomeTrend === 0 ? 'neutral' : 'negative',
            help: 'Évolution de vos revenus par rapport au premier mois enregistré.'
        },
        {
            label: 'Tendance des dépenses',
            value: `${expenseTrend > 0 ? '+' : ''}${expenseTrend.toFixed(1)}%`,
            class: expenseTrend <= 0 ? 'positive' : expenseTrend <= incomeTrend ? 'neutral' : 'negative',
            help: 'Évolution de vos dépenses par rapport au premier mois enregistré.'
        }
    ];
    
    statItems.forEach(item => {
        const statDiv = document.createElement('div');
        statDiv.className = `stat-item ${item.class}`;
        
        const label = document.createElement('span');
        label.className = 'stat-label';
        label.textContent = item.label;
        
        const tooltip = document.createElement('span');
        tooltip.className = 'tooltip';
        tooltip.textContent = '?';
        tooltip.title = item.help;
        
        const value = document.createElement('span');
        value.className = 'stat-value';
        value.textContent = item.value;
        
        statDiv.appendChild(label);
        statDiv.appendChild(tooltip);
        statDiv.appendChild(value);
        
        statsContainer.appendChild(statDiv);
    });
}

/**
 * Génère des conseils budgétaires personnalisés
 */
function generateBudgetAdvice() {
    const adviceContainer = document.getElementById('budgetAdvice');
    if (!adviceContainer) return;
    
    // Calculer les totaux
    const { totalIncome, totalExpenses, balance } = calculateBudget();
    
    // Récupérer les données des dépenses
    const expenseData = JSON.parse(localStorage.getItem('budgetExpenses') || '{}');
    
    // Liste de conseils potentiels
    const adviceList = [];
    
    // Vérifier le budget global
    if (balance < 0) {
        adviceList.push({
            title: 'Budget déficitaire',
            content: 'Vos dépenses dépassent vos revenus. Essayez de réduire certaines dépenses non essentielles ou cherchez des sources de revenus supplémentaires.',
            priority: 'high'
        });
    } else if (balance < totalIncome * 0.1) {
        adviceList.push({
            title: 'Faible épargne',
            content: 'Votre marge d\'épargne est faible (moins de 10% de vos revenus). Essayez d\'augmenter ce taux pour faire face aux imprévus.',
            priority: 'medium'
        });
    }
    
    // Vérifier la part du loyer
    if (expenseData.rent && expenseData.rent > totalIncome * 0.33) {
        adviceList.push({
            title: 'Loyer élevé',
            content: 'Votre loyer représente plus de 33% de vos revenus. Envisagez de chercher un logement moins cher ou une colocation.',
            priority: 'medium'
        });
    }
    
    // Vérifier les aides possibles
    if (!JSON.parse(localStorage.getItem('budgetIncome') || '{}').housing) {
        adviceList.push({
            title: 'Aides au logement',
            content: 'Vous n\'avez pas indiqué recevoir d\'aide au logement. Vérifiez votre éligibilité aux APL sur le site de la CAF.',
            priority: 'medium'
        });
    }
    
    // Conseils généraux
    adviceList.push({
        title: 'Suivi des dépenses',
        content: 'Prenez l\'habitude de noter toutes vos dépenses quotidiennes pour mieux identifier les économies possibles.',
        priority: 'low'
    });
    
    adviceList.push({
        title: 'Économies sur les courses',
        content: 'Faites une liste avant vos courses, comparez les prix et privilégiez les marques de distributeurs pour économiser sur votre budget alimentaire.',
        priority: 'low'
    });
    
    // Afficher les conseils
    adviceContainer.innerHTML = '';
    
    // Trier par priorité
    adviceList.sort((a, b) => {
        const priorities = { high: 0, medium: 1, low: 2 };
        return priorities[a.priority] - priorities[b.priority];
    });
    
    adviceList.forEach(advice => {
        const adviceDiv = document.createElement('div');
        adviceDiv.className = `advice-item ${advice.priority}`;
        
        const title = document.createElement('h4');
        title.textContent = advice.title;
        
        const content = document.createElement('p');
        content.textContent = advice.content;
        
        adviceDiv.appendChild(title);
        adviceDiv.appendChild(content);
        
        adviceContainer.appendChild(adviceDiv);
    });
}

/**
 * Initialise la fonctionnalité de comparaison de scénarios
 */
function initScenarioComparison() {
    const compareBtn = document.getElementById('compareScenarioBtn');
    const scenarioSelector = document.getElementById('scenarioSelector');
    
    if (compareBtn && scenarioSelector) {
        // Charger les scénarios enregistrés
        loadSavedScenarios();
        
        compareBtn.addEventListener('click', function() {
            const selectedScenario = scenarioSelector.value;
            
            if (selectedScenario === 'current') {
                showNotification('Veuillez sélectionner un scénario à comparer.', 'warning');
                return;
            }
            
            compareWithScenario(selectedScenario);
        });
    }
    
    // Bouton pour enregistrer le scénario actuel
    const saveScenarioBtn = document.getElementById('saveScenarioBtn');
    if (saveScenarioBtn) {
        saveScenarioBtn.addEventListener('click', saveCurrentScenario);
    }
}

/**
 * Charge les scénarios enregistrés dans le sélecteur
 */
function loadSavedScenarios() {
    const scenarioSelector = document.getElementById('scenarioSelector');
    if (!scenarioSelector) return;
    
    // Effacer les options existantes sauf la première
    while (scenarioSelector.options.length > 1) {
        scenarioSelector.remove(1);
    }
    
    // Charger les scénarios depuis localStorage
    const scenarios = JSON.parse(localStorage.getItem('savedScenarios') || '{}');
    
    Object.keys(scenarios).forEach(name => {
        const option = document.createElement('option');
        option.value = name;
        option.textContent = name;
        scenarioSelector.appendChild(option);
    });
}

/**
 * Enregistre le scénario budgétaire actuel
 */
function saveCurrentScenario() {
    const nameInput = document.getElementById('scenarioNameInput');
    if (!nameInput || !nameInput.value.trim()) {
        showNotification('Veuillez entrer un nom pour le scénario.', 'warning');
        return;
    }
    
    const name = nameInput.value.trim();
    
    // Récupérer les données actuelles
    const scenarioData = {
        income: JSON.parse(localStorage.getItem('budgetIncome') || '{}'),
        expenses: JSON.parse(localStorage.getItem('budgetExpenses') || '{}'),
        mode: localStorage.getItem('budgetMode') || 'withAlternance',
        timestamp: new Date().toISOString()
    };
    
    // Charger les scénarios existants
    const scenarios = JSON.parse(localStorage.getItem('savedScenarios') || '{}');
    
    // Ajouter le nouveau scénario
    scenarios[name] = scenarioData;
    
    // Enregistrer
    localStorage.setItem('savedScenarios', JSON.stringify(scenarios));
    
    // Mettre à jour le sélecteur
    loadSavedScenarios();
    
    // Effacer le champ de texte
    nameInput.value = '';
    
    showNotification(`Scénario "${name}" enregistré avec succès !`, 'success');
}

/**
 * Compare le budget actuel avec un scénario enregistré
 * @param {string} scenarioName - Nom du scénario à comparer
 */
function compareWithScenario(scenarioName) {
    // Charger les scénarios
    const scenarios = JSON.parse(localStorage.getItem('savedScenarios') || '{}');
    
    if (!scenarios[scenarioName]) {
        showNotification('Scénario introuvable.', 'error');
        return;
    }
    
    const scenarioData = scenarios[scenarioName];
    
    // Calculer le budget actuel
    const currentBudget = calculateBudget();
    
    // Sauvegarder temporairement les données actuelles
    const tempData = {
        income: localStorage.getItem('budgetIncome'),
        expenses: localStorage.getItem('budgetExpenses'),
        mode: localStorage.getItem('budgetMode')
    };
    
    // Charger les données du scénario
    localStorage.setItem('budgetIncome', JSON.stringify(scenarioData.income));
    localStorage.setItem('budgetExpenses', JSON.stringify(scenarioData.expenses));
    localStorage.setItem('budgetMode', scenarioData.mode);
    
    // Calculer le budget du scénario
    const scenarioBudget = calculateBudget();
    
    // Restaurer les données actuelles
    localStorage.setItem('budgetIncome', tempData.income);
    localStorage.setItem('budgetExpenses', tempData.expenses);
    localStorage.setItem('budgetMode', tempData.mode);
    
    // Recalculer le budget actuel pour restaurer l'affichage
    calculateBudget();
    
    // Afficher la comparaison
    displayComparison(currentBudget, scenarioBudget, scenarioName);
}

/**
 * Affiche la comparaison entre le budget actuel et un scénario
 * @param {Object} currentBudget - Données du budget actuel
 * @param {Object} scenarioBudget - Données du budget du scénario
 * @param {string} scenarioName - Nom du scénario
 */
function displayComparison(currentBudget, scenarioBudget, scenarioName) {
    const comparisonContainer = document.getElementById('comparisonResult');
    if (!comparisonContainer) return;
    
    comparisonContainer.innerHTML = '';
    comparisonContainer.classList.remove('hidden');
    
    // Créer le titre
    const title = document.createElement('h3');
    title.textContent = `Comparaison avec le scénario "${scenarioName}"`;
    comparisonContainer.appendChild(title);
    
    // Créer le tableau de comparaison
    const table = document.createElement('table');
    table.className = 'comparison-table';
    
    // En-tête du tableau
    const thead = document.createElement('thead');
    const headerRow = document.createElement('tr');
    
    ['Catégorie', 'Budget actuel', 'Scénario', 'Différence'].forEach(text => {
        const th = document.createElement('th');
        th.textContent = text;
        headerRow.appendChild(th);
    });
    
    thead.appendChild(headerRow);
    table.appendChild(thead);
    
    // Corps du tableau
    const tbody = document.createElement('tbody');
    
    // Ajouter les lignes de données
    const rows = [
        {
            category: 'Revenus totaux',
            current: currentBudget.totalIncome,
            scenario: scenarioBudget.totalIncome
        },
        {
            category: 'Dépenses totales',
            current: currentBudget.totalExpenses,
            scenario: scenarioBudget.totalExpenses
        },
        {
            category: 'Solde mensuel',
            current: currentBudget.balance,
            scenario: scenarioBudget.balance
        }
    ];
    
    rows.forEach(row => {
        const tr = document.createElement('tr');
        
        const diff = row.scenario - row.current;
        
        // Catégorie
        const tdCategory = document.createElement('td');
        tdCategory.textContent = row.category;
        tr.appendChild(tdCategory);
        
        // Budget actuel
        const tdCurrent = document.createElement('td');
        tdCurrent.textContent = row.current.toFixed(2) + ' €';
        tr.appendChild(tdCurrent);
        
        // Scénario
        const tdScenario = document.createElement('td');
        tdScenario.textContent = row.scenario.toFixed(2) + ' €';
        tr.appendChild(tdScenario);
        
        // Différence
        const tdDiff = document.createElement('td');
        tdDiff.textContent = (diff > 0 ? '+' : '') + diff.toFixed(2) + ' €';
        tdDiff.className = diff > 0 ? 'positive' : diff < 0 ? 'negative' : '';
        tr.appendChild(tdDiff);
        
        tbody.appendChild(tr);
    });

    
    table.appendChild(tbody);
    comparisonContainer.appendChild(table);
    
    // Ajouter un bouton pour fermer la comparaison
    const closeBtn = document.createElement('button');
    closeBtn.textContent = 'Fermer';
    closeBtn.className = 'btn btn-secondary';
    closeBtn.addEventListener('click', function() {
        comparisonContainer.classList.add('hidden');
    });
    
    comparisonContainer.appendChild(closeBtn);
}



// Initialiser les fonctionnalités supplémentaires
document.addEventListener('DOMContentLoaded', function() {
    // Fonctionnalités existantes
    initCharts();
    initModeToggle();
    initFormHandlers();
    initAplSimulation();
    checkBudgetStatus();
    
    // Nouvelles fonctionnalités
    initBackupRestore();
    initScenarioComparison();
    
    // Générer les statistiques et conseils
    generateStats();
    generateBudgetAdvice();
    
    // Boutons d'export
    const pdfExportBtn = document.getElementById('pdfExportBtn');
    const excelExportBtn = document.getElementById('excelExportBtn');
    
    if (pdfExportBtn) {
        pdfExportBtn.addEventListener('click', exportToPdf);
    }
    
    if (excelExportBtn) {
        excelExportBtn.addEventListener('click', exportToExcel);
    }
});