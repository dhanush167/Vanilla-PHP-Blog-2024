
function openModal(id) {
    document.getElementById('delete_id').value = id;
    document.getElementById('deleteModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Optional: close if user clicks outside the modal
window.onclick = function(event) {
    const modal = document.getElementById('deleteModal');
    if (event.target == modal) {
        closeModal();
    }
}

/*logout model*/
// assets/js/logout-modal.js
document.addEventListener("DOMContentLoaded", () => {
    const openBtn = document.querySelector('[data-modal-open="logoutModal"]');
    const modal = document.getElementById("logoutModal");
    const closeBtn = document.querySelector('[data-modal-close="logoutModal"]');

    if (!openBtn || !modal || !closeBtn) return;

    const openLogoutModal = () => {
        modal.classList.add("show");
        modal.setAttribute("aria-hidden", "false");
    };

    const closeLogoutModal = () => {
        modal.classList.remove("show");
        modal.setAttribute("aria-hidden", "true");
    };

    openBtn.addEventListener("click", (e) => {
        e.preventDefault();
        openLogoutModal();
    });

    closeBtn.addEventListener("click", closeLogoutModal);

    modal.addEventListener("click", (e) => {
        if (e.target === modal) closeLogoutModal();
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && modal.classList.contains("show")) {
            closeLogoutModal();
        }
    });
});
