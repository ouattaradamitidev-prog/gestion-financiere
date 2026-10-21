const allTransactions = [
            { id: 'TXN-001', user: 'Amara Koné', type: 'Entrée', montant: 150000, motif: 'Vente de produits', date: '2025-11-07 09:30', statut: 'validée', validateur: 'Admin' },
            { id: 'TXN-002', user: 'Fatou Diallo', type: 'Sortie', montant: 75000, motif: 'Achat de fournitures', date: '2025-11-06 14:20', statut: 'validée', validateur: 'Admin' },
            { id: 'TXN-003', user: 'Ibrahim Traoré', type: 'Entrée', montant: 200000, motif: 'Paiement client ABC', date: '2025-11-06 11:15', statut: 'en attente', validateur: '-' },
            { id: 'TXN-004', user: 'Amina Ouattara', type: 'Sortie', montant: 50000, motif: 'Frais de transport', date: '2025-11-05 16:45', statut: 'refusée', validateur: 'Admin' },
            { id: 'TXN-005', user: 'Moussa Sangaré', type: 'Entrée', montant: 300000, motif: 'Subvention projet', date: '2025-11-05 10:00', statut: 'en attente', validateur: '-' },
            { id: 'TXN-006', user: 'Amara Koné', type: 'Sortie', montant: 120000, motif: 'Salaire personnel', date: '2025-11-04 09:00', statut: 'validée', validateur: 'Admin' },
            { id: 'TXN-007', user: 'Fatou Diallo', type: 'Entrée', montant: 400000, motif: 'Vente équipements', date: '2025-11-03 15:30', statut: 'validée', validateur: 'Admin' },
            { id: 'TXN-008', user: 'Ibrahim Traoré', type: 'Sortie', montant: 85000, motif: 'Achat matériel', date: '2025-11-03 11:00', statut: 'validée', validateur: 'Admin' },
            { id: 'TXN-009', user: 'Amina Ouattara', type: 'Entrée', montant: 250000, motif: 'Paiement service', date: '2025-11-02 13:45', statut: 'validée', validateur: 'Admin' },
            { id: 'TXN-010', user: 'Moussa Sangaré', type: 'Sortie', montant: 60000, motif: 'Réparations', date: '2025-11-02 10:20', statut: 'validée', validateur: 'Admin' },
            { id: 'TXN-011', user: 'Amara Koné', type: 'Entrée', montant: 180000, motif: 'Consultation', date: '2025-11-01 16:00', statut: 'validée', validateur: 'Admin' },
            { id: 'TXN-012', user: 'Fatou Diallo', type: 'Sortie', montant: 95000, motif: 'Fournitures bureau', date: '2025-11-01 14:30', statut: 'validée', validateur: 'Admin' },
            { id: 'TXN-013', user: 'Ibrahim Traoré', type: 'Entrée', montant: 350000, motif: 'Contrat maintenance', date: '2025-10-31 11:00', statut: 'validée', validateur: 'Admin' },
            { id: 'TXN-014', user: 'Amina Ouattara', type: 'Sortie', montant: 110000, motif: 'Formation staff', date: '2025-10-31 09:15', statut: 'refusée', validateur: 'Admin' },
            { id: 'TXN-015', user: 'Moussa Sangaré', type: 'Entrée', montant: 320000, motif: 'Vente prestations', date: '2025-10-30 15:45', statut: 'validée', validateur: 'Admin' },
        ];

        let filteredTransactions = [...allTransactions];

        function getStatutClass(statut) {
            switch(statut) {
                case 'validée': return 'bg-green-100 text-green-800';
                case 'en attente': return 'bg-yellow-100 text-yellow-800';
                case 'refusée': return 'bg-red-100 text-red-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        }

        function renderTransactions() {
            const tbody = document.getElementById('historiqueTable');
            tbody.innerHTML = filteredTransactions.map(tx => `
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">${tx.id}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">${tx.user}</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold ${tx.type === 'Entrée' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${tx.type}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">${tx.montant.toLocaleString()} FCFA</td>
                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">${tx.motif}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">${tx.date}</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold ${getStatutClass(tx.statut)}">
                            ${tx.statut.charAt(0).toUpperCase() + tx.statut.slice(1)}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">${tx.validateur}</td>
                </tr>
            `).join('');

            updateStats();
        }

        function updateStats() {
            const total = filteredTransactions.length;
            const entrees = filteredTransactions.filter(t => t.type === 'Entrée').reduce((sum, t) => sum + t.montant, 0);
            const sorties = filteredTransactions.filter(t => t.type === 'Sortie').reduce((sum, t) => sum + t.montant, 0);
            const solde = entrees - sorties;

            document.getElementById('statTotal').textContent = total;
            document.getElementById('statEntrees').textContent = entrees.toLocaleString() + ' FCFA';
            document.getElementById('statSorties').textContent = sorties.toLocaleString() + ' FCFA';
            document.getElementById('statSolde').textContent = (solde >= 0 ? '+' : '') + solde.toLocaleString() + ' FCFA';
        }

        function applyFilters() {
            const type = document.getElementById('filterType').value;
            const statut = document.getElementById('filterStatut').value;
            const user = document.getElementById('filterUser').value.toLowerCase();

            filteredTransactions = allTransactions.filter(tx => {
                const matchType = !type || tx.type === type;
                const matchStatut = !statut || tx.statut === statut;
                const matchUser = !user || tx.user.toLowerCase().includes(user);
                return matchType && matchStatut && matchUser;
            });

            renderTransactions();
        }

        function resetFilters() {
            document.getElementById('dateDebut').value = '';
            document.getElementById('dateFin').value = '';
            document.getElementById('filterType').value = '';
            document.getElementById('filterStatut').value = '';
            document.getElementById('filterUser').value = '';
            filteredTransactions = [...allTransactions];
            renderTransactions();
        }

        function exportToPDF() {
            alert('Export PDF : Cette fonction sera implémentée côté serveur PHP');
        }

        renderTransactions();