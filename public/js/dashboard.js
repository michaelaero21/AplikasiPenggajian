document.addEventListener('DOMContentLoaded', function () {
    const countdownElement = document.getElementById("time-left");

    if (!countdownElement) return;

    const targetDate = countdownElement.dataset.target;
    const countDownDate = new Date(targetDate).getTime();

    function updateCountdown() {
        const now = new Date().getTime();
        const distance = countDownDate - now;

        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        if (distance >= 0) {
            countdownElement.textContent = `${hours}h ${minutes}m ${seconds}s`;
        } else {
            countdownElement.textContent = "Waktu Gajian Tiba!";
            clearInterval(timer);
        }
    }

    const timer = setInterval(updateCountdown, 1000);
    updateCountdown(); // panggil pertama langsung
});
