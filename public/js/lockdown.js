/* True Commerce — production lockdown script.
   Loaded only when APP_LOCKDOWN=true. Blocks common copy/save/inspect
   gestures. NOT a security barrier — it's a deterrent layered on top
   of server-side protections. */
(function () {
    'use strict';

    function inEditable(el) {
        if (!el) return false;
        const t = (el.tagName || '').toLowerCase();
        return t === 'input' || t === 'textarea' || t === 'select' || el.isContentEditable;
    }

    // Disable right-click context menu (except on form fields)
    document.addEventListener('contextmenu', function (e) {
        if (!inEditable(e.target)) e.preventDefault();
    }, true);

    // Disable image drag
    document.addEventListener('dragstart', function (e) {
        if (e.target && /^(IMG|PICTURE|VIDEO|CANVAS|SVG)$/i.test(e.target.tagName)) {
            e.preventDefault();
        }
    }, true);

    // Block clipboard copy/cut on non-input content
    ['copy', 'cut'].forEach(evt => {
        document.addEventListener(evt, function (e) {
            if (!inEditable(e.target)) e.preventDefault();
        }, true);
    });

    // Block keyboard shortcuts: F12, Ctrl+U, Ctrl+S, Ctrl+P,
    // Ctrl+Shift+I/J/C, Cmd equivalents.
    document.addEventListener('keydown', function (e) {
        const k    = e.key;
        const ctrl = e.ctrlKey || e.metaKey;
        const shift = e.shiftKey;
        const inField = inEditable(e.target);

        if (k === 'F12') { e.preventDefault(); return; }
        if (ctrl && shift && (k === 'I' || k === 'i' || k === 'J' || k === 'j' || k === 'C' || k === 'c')) {
            e.preventDefault(); return;
        }
        if (ctrl && (k === 'U' || k === 'u')) { e.preventDefault(); return; }
        if (ctrl && !inField && (k === 'S' || k === 's' || k === 'P' || k === 'p' || k === 'A' || k === 'a')) {
            e.preventDefault(); return;
        }
    }, true);

    // Best-effort devtools detection via timing & dimension diff.
    // When detected, a shield is shown that hides content.
    var shieldShown = false;
    function showShield() {
        if (shieldShown) return;
        shieldShown = true;
        if (!document.querySelector('.lockdown-shield')) {
            const div = document.createElement('div');
            div.className = 'lockdown-shield';
            div.innerHTML = '<div><h3 class="mb-2">Inspection blocked</h3>'
                          + '<p class="opacity-75">This page is protected. Please close developer tools to continue.</p></div>';
            document.body.appendChild(div);
        }
        document.body.classList.add('lockdown-blocked');
    }
    function hideShield() {
        shieldShown = false;
        document.body.classList.remove('lockdown-blocked');
    }

    var threshold = 160;
    function check() {
        var widthDiff  = window.outerWidth  - window.innerWidth;
        var heightDiff = window.outerHeight - window.innerHeight;
        if (widthDiff > threshold || heightDiff > threshold) showShield();
        else hideShield();
    }
    window.addEventListener('resize', check);
    setInterval(check, 1000);

    // Print blocker
    window.addEventListener('beforeprint', function (e) { e.preventDefault?.(); });
})();
