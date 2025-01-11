document.addEventListener('DOMContentLoaded', function() {
    // Theme toggle functionality remains the same
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;

    themeToggle?.addEventListener('click', function() {
        body.classList.toggle('dark-mode');
        const icon = themeToggle.querySelector('i');
        icon.classList.toggle('fa-moon');
        icon.classList.toggle('fa-sun');
    });

    // Improved diary entry handling
    const entries = document.querySelectorAll('.entry');
    entries.forEach(entry => {
        const decryptBtn = entry.querySelector('.decrypt-btn');
        const encryptBtn = entry.querySelector('.encrypt-btn');
        const editBtn = entry.querySelector('.edit-btn');
        const deleteBtn = entry.querySelector('.delete-btn');
        const titleElement = entry.querySelector('.encrypted-data[data-encrypted]');
        const contentElement = entry.querySelector('.encrypted-data[data-encrypted]:not(:first-child)');
        const entryId = entry.getAttribute('data-entry-id');

        // Improved error handling and user feedback
        const showError = (message) => {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = message;
            errorDiv.style.color = 'red';
            errorDiv.style.marginTop = '10px';
            
            // Remove any existing error messages
            const existingError = entry.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
            
            entry.appendChild(errorDiv);
            setTimeout(() => errorDiv.remove(), 5000);
        };

        const performAction = async (action, data = {}) => {
            try {
                const password = await new Promise((resolve) => {
                    const pwd = prompt("Enter your password to perform this action:");
                    resolve(pwd);
                });

                if (!password) {
                    throw new Error('Password is required');
                }

                const formData = new FormData();
                formData.append('action', action);
                formData.append('entry_id', entryId);
                formData.append('password', password);

                Object.entries(data).forEach(([key, value]) => {
                    formData.append(key, value);
                });

                const response = await fetch('process_entry.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (!result.success) {
                    throw new Error(result.message || 'Operation failed');
                }

                return result;
            } catch (error) {
                showError(error.message);
                throw error;
            }
        };

        // Improved decrypt functionality
        decryptBtn?.addEventListener('click', async function() {
            try {
                const result = await performAction('decrypt');
                
                if (!result.title || !result.content) {
                    throw new Error('Invalid decryption result');
                }
                
                titleElement.textContent = result.title;
                contentElement.textContent = result.content;
                decryptBtn.style.display = 'none';
                encryptBtn.style.display = 'inline-block';
            } catch (error) {
                showError('Decryption failed: ' + error.message);
            }
        });

        // Improved encrypt functionality
        encryptBtn?.addEventListener('click', async function() {
            try {
                await performAction('encrypt', {
                    title: titleElement.textContent,
                    content: contentElement.textContent
                });
                
                titleElement.textContent = '[Encrypted Title]';
                contentElement.textContent = '[Encrypted Content]';
                encryptBtn.style.display = 'none';
                decryptBtn.style.display = 'inline-block';
            } catch (error) {
                showError('Encryption failed: ' + error.message);
            }
        });

        // Improved edit functionality with modal
        editBtn?.addEventListener('click', async function() {
            try {
                const decryptResult = await performAction('decrypt');
                
                // Create and show modal
                const modal = document.createElement('div');
                modal.className = 'edit-modal';
                modal.innerHTML = `
                    <div class="modal-content">
                        <h3>Edit Entry</h3>
                        <input type="text" id="edit-title" value="${decryptResult.title}" placeholder="Title">
                        <textarea id="edit-content" rows="5" placeholder="Content">${decryptResult.content}</textarea>
                        <div class="modal-buttons">
                            <button class="save-btn">Save</button>
                            <button class="cancel-btn">Cancel</button>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(modal);
                
                // Add event listeners for modal buttons
                modal.querySelector('.save-btn').addEventListener('click', async () => {
                    const newTitle = modal.querySelector('#edit-title').value;
                    const newContent = modal.querySelector('#edit-content').value;
                    
                    try {
                        await performAction('edit', { 
                            title: newTitle, 
                            content: newContent 
                        });
                        location.reload();
                    } catch (error) {
                        showError('Failed to save changes: ' + error.message);
                    }
                });
                
                modal.querySelector('.cancel-btn').addEventListener('click', () => {
                    modal.remove();
                });
            } catch (error) {
                showError('Edit failed: ' + error.message);
            }
        });

        // Improved delete functionality
        deleteBtn?.addEventListener('click', async function() {
            if (confirm('Are you sure you want to delete this entry? This action cannot be undone.')) {
                try {
                    await performAction('delete');
                    entry.style.animation = 'fadeOut 0.5s';
                    setTimeout(() => entry.remove(), 500);
                } catch (error) {
                    showError('Delete failed: ' + error.message);
                }
            }
        });
    });
});

// Add required CSS
const style = document.createElement('style');
style.textContent = `
    .edit-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .modal-content {
        background: white;
        padding: 20px;
        border-radius: 8px;
        width: 80%;
        max-width: 500px;
    }

    .modal-content input,
    .modal-content textarea {
        width: 100%;
        margin-bottom: 10px;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .modal-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }

    .error-message {
        padding: 10px;
        margin-top: 10px;
        border-radius: 4px;
        background-color: rgba(255, 0, 0, 0.1);
    }
`;
document.head.appendChild(style);