class Cart {
    constructor() {
        this.items = JSON.parse(localStorage.getItem('cart')) || [];
        this.initEvents();
    }

    initEvents() {
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', (e) => this.addItem(e.target.dataset));
        });
    }

    addItem(itemData) {
        const existingItem = this.items.find(item => item.id === itemData.id);
        if (existingItem) {
            existingItem.quantity++;
        } else {
            this.items.push({
                ...itemData,
                quantity: 1
            });
        }
        this.updateStorage();
        this.updateCounter();
    }

    updateCounter() {
        const counter = document.querySelector('.cart-counter');
        if (counter) {
            counter.textContent = this.items.reduce((total, item) => total + item.quantity, 0);
        }
    }

    updateStorage() {
        localStorage.setItem('cart', JSON.stringify(this.items));
    }
}

// Initialisation
if (document.querySelector('.add-to-cart')) {
    new Cart();
}