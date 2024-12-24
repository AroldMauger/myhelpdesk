document.addEventListener('DOMContentLoaded', () => {
    const logoutLinks = document.querySelectorAll('.logout-link');

    logoutLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();

            const confirmation = confirm("Voulez-vous vraiment vous déconnecter?");

            if (confirmation) {
                window.location.href = this.href;
            }
        });
    });
});
