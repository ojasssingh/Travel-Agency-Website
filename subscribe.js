// ================= SUBSCRIBE VALIDATION =================
 
const subscriptionEmailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
 
function validateSubscriptionEmail(input, showFeedback = false) {
    const normalizedEmail = input.value.trim().toLowerCase();
    if (showFeedback || document.activeElement !== input) {
        input.value = normalizedEmail;
    }
    let message = "";
    if (normalizedEmail === "") {
        message = "Email is required.";
    } else if (normalizedEmail.length > 150) {
        message = "Email address must be 150 characters or fewer.";
    } else if (!subscriptionEmailPattern.test(normalizedEmail)) {
        message = "Enter a valid email address like name@email.com.";
    }
    input.setCustomValidity(message);
    if (showFeedback && message !== "") input.reportValidity();
    return message === "";
}
 
document.querySelectorAll(".subscribe-form").forEach((form) => {
    const emailInput = form.querySelector('input[name="email"]');
    if (!emailInput) return;
    emailInput.addEventListener("input", () => validateSubscriptionEmail(emailInput));
    emailInput.addEventListener("blur",  () => validateSubscriptionEmail(emailInput, true));
    form.addEventListener("submit", (e) => {
        if (!validateSubscriptionEmail(emailInput, true)) e.preventDefault();
    });
});
 
// ================= CARD CLICK / BOOK BUTTONS / FADE =================
 
document.querySelectorAll(".book-btn[data-book-place]").forEach((btn) => {
    btn.addEventListener("click", (e) => {
        e.stopPropagation();
        window.location.href = "booking.html?place=" + encodeURIComponent(btn.dataset.bookPlace);
    });
});
 
document.querySelectorAll(".card[data-detail]").forEach((card, i) => {
    card.style.transitionDelay = i * 0.1 + "s";
    card.addEventListener("click", () => { window.location.href = card.dataset.detail; });
    card.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === " ") { e.preventDefault(); window.location.href = card.dataset.detail; }
    });
});
 
const fadeObserver = new IntersectionObserver((entries) => {
    entries.forEach((e) => {
        if (e.isIntersecting) { e.target.classList.add("is-visible"); fadeObserver.unobserve(e.target); }
    });
}, { threshold: 0.2 });
document.querySelectorAll(".card[data-detail]").forEach((c) => fadeObserver.observe(c));
 
 
// ================= INFINITE CAROUSEL =================
 
window.addEventListener("DOMContentLoaded", function () {
 
    var track   = document.querySelector(".slider-track");
    var prevBtn = document.querySelector(".slider-arrow--left");
    var nextBtn = document.querySelector(".slider-arrow--right");
    if (!track || !prevBtn || !nextBtn) return;
 
    var SPEED   = 500;
    var AUTO_MS = 3500;
 
    // ── STEP 1: grab originals ──────────────────────────────────────
    var originals = Array.from(track.children); // [C0,C1,C2,C3,C4]
    var N = originals.length;                   // 5
 
    // ── STEP 2: append RIGHT clones (go past last card → loop) ─────
    originals.forEach(function(c) {
        track.appendChild(c.cloneNode(true));
    });
 
    // ── STEP 3: prepend LEFT clones (go before first card → loop) ──
    // Use fragment so order is preserved (C0,C1,C2,C3,C4 not reversed)
    var frag = document.createDocumentFragment();
    originals.forEach(function(c) {
        frag.appendChild(c.cloneNode(true));
    });
    track.insertBefore(frag, track.firstChild);
 
    // track = [L0,L1,L2,L3,L4 | C0,C1,C2,C3,C4 | R0,R1,R2,R3,R4]
    //  idx:    0  1  2  3  4    5  6  7  8  9    10 11 12 13 14
    // Start at idx=5 → shows C0 (Andaman)
 
    // ── STEP 4: attach listeners to all clones ──────────────────────
    function wire(root) {
        if (root.classList && root.classList.contains("card") && root.dataset.detail) {
            root.addEventListener("click", function() { window.location.href = root.dataset.detail; });
            root.addEventListener("keydown", function(e) {
                if (e.key === "Enter" || e.key === " ") { e.preventDefault(); window.location.href = root.dataset.detail; }
            });
        }
        var btn = (root.querySelector ? root.querySelector(".book-btn[data-book-place]") : null);
        if (btn) {
            btn.addEventListener("click", function(e) {
                e.stopPropagation();
                window.location.href = "booking.html?place=" + encodeURIComponent(btn.dataset.bookPlace);
            });
        }
    }
    Array.from(track.children).forEach(wire);
 
    // ── STEP 5: measure — cache after fonts/images settle ──────────
    var STEP = 0; // card width + gap, computed once on first slide
 
    function getStep() {
        if (STEP) return STEP;
        var gap = parseFloat(getComputedStyle(track).gap) || 30;
        STEP = track.children[0].offsetWidth + gap;
        return STEP;
    }
 
    // ── STEP 6: position ────────────────────────────────────────────
    var idx      = N;     // 5 = first real card
    var animating = false;
 
    function moveTo(i, anim) {
        track.style.transition = anim
            ? "transform " + SPEED + "ms cubic-bezier(0.25,0.46,0.45,0.94)"
            : "none";
        track.style.transform = "translateX(" + (-(i * getStep())) + "px)";
    }
 
    // ── STEP 7: after slide, snap silently if on a clone ───────────
    track.addEventListener("transitionend", function() {
        animating = false;
 
        if (idx >= N * 2) {
            // We slid into RIGHT clones → jump to matching real card
            idx = idx - N;
            moveTo(idx, false);
        } else if (idx < N) {
            // We slid into LEFT clones → jump to matching real card
            idx = idx + N;
            moveTo(idx, false);
        }
    });
 
    function slide(dir) {
        if (animating) return;
        animating = true;
        idx += dir;
        moveTo(idx, true);
    }
 
    // ── STEP 8: set start position after layout is painted ─────────
    // Double rAF guarantees offsetWidth is available
    requestAnimationFrame(function() {
        requestAnimationFrame(function() {
            STEP = 0; // force fresh measurement
            moveTo(idx, false);
        });
    });
 
    // ── STEP 9: controls ───────────────────────────────────────────
    prevBtn.addEventListener("click", function() { slide(-1); });
    nextBtn.addEventListener("click", function() { slide(1);  });
 
    var timer = setInterval(function() { slide(1); }, AUTO_MS);
    var wrapper = document.querySelector(".slider-wrapper");
    wrapper.addEventListener("mouseenter", function() { clearInterval(timer); });
    wrapper.addEventListener("mouseleave", function() {
        timer = setInterval(function() { slide(1); }, AUTO_MS);
    });
 
    // Touch
    var tx = 0;
    track.addEventListener("touchstart", function(e) { tx = e.touches[0].clientX; }, { passive: true });
    track.addEventListener("touchend",   function(e) {
        var diff = tx - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 40) slide(diff > 0 ? 1 : -1);
    });
 
    // Drag
    var dragX = 0, dragging = false;
    track.addEventListener("mousedown", function(e) { dragX = e.clientX; dragging = true; track.classList.add("is-grabbing"); });
    window.addEventListener("mouseup", function(e) {
        if (!dragging) return;
        dragging = false;
        track.classList.remove("is-grabbing");
        if (Math.abs(dragX - e.clientX) > 40) slide(dragX - e.clientX > 0 ? 1 : -1);
    });
 
    window.addEventListener("resize", function() { STEP = 0; moveTo(idx, false); });
});
 