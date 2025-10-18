class ActionDropdown {
    constructor() {
        this.currentDropdown = null;
        this.init();
    }

    init() {
        if (!document.getElementById('actionOverlay')) {
            const overlay = document.createElement('div');
            overlay.id = 'actionOverlay';
            overlay.className = 'action-overlay';
            document.body.appendChild(overlay);
            
            overlay.addEventListener('click', () => {
                this.closeAll();
            });
        }
    }

    createDropdown(actions) {
        const dropdownId = 'dropdown-' + Date.now();
        
        const dropdownHTML = `
            <div class="action-dropdown">
                <button class="action-dots" onclick="actionDropdown.toggle('${dropdownId}')">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="action-menu" id="${dropdownId}">
                    ${actions.map(action => `
                        <button class="action-menu-item ${action.type}" onclick="${action.onclick}">
                            <i class="fas fa-${this.getActionIcon(action.type)}"></i>
                            ${action.label}
                        </button>
                    `).join('')}
                </div>
            </div>
        `;
        
        return dropdownHTML;
    }

    getActionIcon(type) {
        const icons = {
            'view': 'eye',
            'edit': 'edit',
            'delete': 'trash'
        };
        return icons[type] || 'circle';
    }

    toggle(dropdownId) {
        const dropdown = document.getElementById(dropdownId);
        const overlay = document.getElementById('actionOverlay');
        const button = dropdown.previousElementSibling;

         const rect = button.getBoundingClientRect();

         dropdown.style.top = (rect.bottom + window.scrollY) + 'px';
         dropdown.style.left = (rect.left + window.scrollX) + 'px';
        
        if (this.currentDropdown && this.currentDropdown !== dropdown) {
            this.currentDropdown.classList.remove('show');
        }
        
        if (dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
            overlay.classList.remove('show');
            this.currentDropdown = null;
        } else {
            dropdown.classList.add('show');
            overlay.classList.add('show');
            this.currentDropdown = dropdown;
        }
    }

    closeAll() {
        const dropdowns = document.querySelectorAll('.action-menu');
        const overlay = document.getElementById('actionOverlay');
        
        dropdowns.forEach(dropdown => {
            dropdown.classList.remove('show');
        });
        
        overlay.classList.remove('show');
        this.currentDropdown = null;
    }
}

const actionDropdown = new ActionDropdown();

window.actionDropdown = actionDropdown;
window.ActionDropdown = ActionDropdown;