</div> </div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Simple frontend idle timer (Backs up the server-side session timeout)
    let idleTime = 0;
    
    // Increment the idle time counter every minute
    setInterval(timerIncrement, 60000); // 1 minute

    // Reset idle timer on mouse movement or keypress
    window.onload = resetTimer;
    window.onmousemove = resetTimer;
    window.onkeypress = resetTimer;
    window.ontouchstart = resetTimer; 

    function resetTimer() {
        idleTime = 0;
    }

    function timerIncrement() {
        idleTime = idleTime + 1;
        // If 15 minutes of inactivity, reload page to trigger server session check/logout
        if (idleTime > 14) { 
            window.location.reload();
        }
    }
</script>

</body>
</html>