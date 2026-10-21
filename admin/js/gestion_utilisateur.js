let users = [
            { id: 1, nom: 'Admin Principal', email: 'admin@entreprise.com', tel: '+225 07 00 00 00 01', role: 'Administrateur', date: '2024-01-15' },
            { id: 2, nom: 'Admin Secondaire', email: 'admin2@entreprise.com', tel: '+225 07 00 00 00 02', role: 'Administrateur', date: '2024-02-10' },
            { id: 3, nom: 'Amara Koné', email: 'amara.kone@entreprise.com', tel: '+225 07 00 00 00 03', role: 'Utilisateur', date: '2024-03-05' },
            { id: 4, nom: 'Fatou Diallo', email: 'fatou.diallo@entreprise.com', tel: '+225 07 00 00 00 04', role: 'Utilisateur', date: '2024-03-12' },
            { id: 5, nom: 'Ibrahim Traoré', email: 'ibrahim.traore@entreprise.com', tel: '+225 07 00 00 00 05', role: 'Utilisateur', date: '2024-04-20' },
            { id: 6, nom: 'Amina Ouattara', email: 'amina.ouattara@entreprise.com', tel: '+225 07 00 00 00 06', role: 'Utilisateur', date: '2024-05-08' },
            { id: 7, nom: 'Moussa Sangaré', email: 'moussa.sangare@entreprise.com', tel: '+225 07 00 00 00 07', role: 'Utilisateur', date: '2024-06-15' },
            { id: 8, nom: 'Claire Moreau', email: 'claire.moreau@entreprise.com', tel: '+225 07 00 00 00 08', role: 'Utilisateur', date: '2024-07-22' },
        ];

        let currentUserId = null;
        let currentMode = 'add';

        function renderUsers() {
            const tbody = document.getElementById('usersTable');
            tbody.innerHTML = users.map(user => `
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">#${user.id}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">${user.nom}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">${user.email}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">${user.tel}</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold ${user.role === 'Administrateur' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'}">
                            ${user.role}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">${user.date}</td>
                    <td class="px-6 py-4 text-sm">
                        <div class="flex justify-center space-x-2">
                            <button onclick="openModal('edit', ${user.id})" class="text-blue-600 hover:text-blue-800 font-semibold">
                                Modifier
                            </button>
                            <button onclick="openDeleteModal(${user.id})" class="text-red-600 hover:text-red-800 font-semibold">
                                Supprimer
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');

            updateStats();
        }

        function updateStats() {
            document.getElementById('totalUsers').textContent = users.length;
            document.getElementById('totalAdmins').textContent = users.filter(u => u.role === 'Administrateur').length;
            document.getElementById('totalNormalUsers').textContent = users.filter(u => u.role === 'Utilisateur').length;
        }

        function openModal(mode, userId = null) {
            currentMode = mode;
            const modal = document.getElementById('userModal');
            const modalTitle = document.getElementById('modalTitle');
            const passwordField = document.getElementById('passwordField');
            
            if (mode === 'add') {
                modalTitle.textContent = 'Ajouter un utilisateur';
                document.getElementById('userForm').reset();
                document.getElementById('userPassword').required = true;
                passwordField.classList.remove('hidden');
            } else {
                currentUserId = userId;
                const user = users.find(u => u.id === userId);
                modalTitle.textContent = 'Modifier un utilisateur';
                document.getElementById('userId').value = user.id;
                document.getElementById('userName').value = user.nom;
                document.getElementById('userEmail').value = user.email;
                document.getElementById('userPhone').value = user.tel;
                document.getElementById('userRole').value = user.role;
                document.getElementById('userPassword').required = false;
                passwordField.classList.add('hidden');
            }
            
            modal.classList.remove('hidden');
        }

        function closeUserModal() {
            document.getElementById('userModal').classList.add('hidden');
        }

        function openDeleteModal(userId) {
            currentUserId = userId;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            currentUserId = null;
        }

        function confirmDelete() {
            users = users.filter(u => u.id !== currentUserId);
            renderUsers();
            closeDeleteModal();
            showMessage('Utilisateur supprimé avec succès', 'success');
        }

        function showMessage(text, type) {
            const messageDiv = document.getElementById('message');
            messageDiv.className = `mb-6 p-4 rounded-lg ${type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
            messageDiv.textContent = text;
            messageDiv.classList.remove('hidden');
            setTimeout(() => messageDiv.classList.add('hidden'), 3000);
        }

        document.getElementById('userForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const nom = document.getElementById('userName').value;
            const email = document.getElementById('userEmail').value;
            const tel = document.getElementById('userPhone').value;
            const role = document.getElementById('userRole').value;
            
            if (currentMode === 'add') {
                const newUser = {
                    id: users.length > 0 ? Math.max(...users.map(u => u.id)) + 1 : 1,
                    nom, email, tel, role,
                    date: new Date().toISOString().split('T')[0]
                };
                users.push(newUser);
                showMessage('Utilisateur ajouté avec succès', 'success');
            } else {
                const userIndex = users.findIndex(u => u.id === currentUserId);
                users[userIndex] = { ...users[userIndex], nom, email, tel, role };
                showMessage('Utilisateur modifié avec succès', 'success');
            }
            
            renderUsers();
            closeUserModal();
        });

        renderUsers();