function bindSchool() {
    $('#registration_delegate_city').on('change', function () {
        const id = $(this).val();

        $.get('/schools', {'city-id': id}, function (data) {
            let options = '<option value="" selected="selected">Izaberite školu</option>';

            for (let i = 0; i < data.length; i++) {
                options += '<option value="' + data[i].id + '">' + data[i].name + '</option>';
            }

            $('#registration_delegate_school').html(options);
        });
    });
}

function loadDriverInfo() {
    if(!$('.js-info-button').is(':visible')){
        return false;
    }

    if(localStorage.getItem("info-button-already-shown")){
        return false;
    }

    const driver = window.driver.js.driver;
    const driverObj = driver();

    driverObj.highlight({
        element: ".js-info-button",
        popover: {
            title: "Uputstva za korišćenje",
            description: "Na svakoj stranici na kojoj je dostupna ova opcija, klikom na dugme pokrećete jednostavan vodič koji će vam pomoći da brže i lakše razumete šta se prikazuje na stranici i koje sve akcije možete preduzeti."
        }
    });

    localStorage.setItem("info-button-already-shown", true);
}

function loadDriver(steps){
    const driver = window.driver.js.driver;
    const driverObj = driver({
        showProgress: true,
        doneBtnText: 'Završi',
        closeBtnText: 'Zatvori',
        nextBtnText: 'Sledeće',
        prevBtnText: 'Prethodno',
        steps: steps,
    });

    $('.js-info-button').on('click', function () {
        driverObj.drive();
    });
}

function loadSelect2() {
    $('.js-select2').select2();
}

function initCopyTooltip() {
    // Check if page has copyable elements and clipboard is supported
    const copyableElements = document.querySelectorAll('[data-copyable]');
    if (copyableElements.length === 0 || !ClipboardJS.isSupported()) {
        console.log('Copy functionality not available');
        return;
    }

    // Show copy instructions
    const copyInfo = document.querySelector('.copy-info');
    if (copyInfo) {
        copyInfo.classList.add('show');
    }

    // Constants
    const HOVER_DELAY = 1000;
    const RESET_DELAY = 1500;
    const TAP_TIMEOUT = 3000;

    // Labels
    const LABELS = {
        desktop: {
            default: 'Kliknite da kopirate',
            success: 'Kopirano!',
            error: 'Greška pri kopiranju'
        },
        mobile: {
            default: 'Kliknite ponovo da kopirate',
            initial: 'Kliknite ponovo da kopirate',
            success: 'Kopirano!',
            error: 'Greška pri kopiranju'
        }
    };

    // Check if device supports hover
    const isTouch = window.matchMedia('(hover: none)').matches;

    // Create and append tooltip element
    const tooltip = document.createElement('div');
    tooltip.className = 'copy-tooltip';
    tooltip.textContent = isTouch ? LABELS.mobile.initial : LABELS.desktop.default;
    document.body.appendChild(tooltip);

    // Create backdrop for mobile
    let backdrop;
    if (isTouch) {
        backdrop = document.createElement('div');
        backdrop.className = 'copy-tooltip-backdrop';
        document.body.appendChild(backdrop);
    }

    function showTooltip(element, text) {
        tooltip.textContent = text;
        if (isTouch) {
            backdrop.classList.add('show');
            tooltip.classList.add('show');
        } else {
            const rect = element.getBoundingClientRect();
            tooltip.style.top = `${rect.top - 30}px`;
            tooltip.style.left = `${rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)}px`;
            tooltip.classList.add('show');
        }
    }

    function hideTooltip() {
        tooltip.classList.remove('show');
        if (backdrop) {
            backdrop.classList.remove('show');
        }
    }

    // Add functionality based on device
    let hoverTimeout;
    let tapTimeout;
    let selectedElement = null;
    let clipboardInstance = null;

    function initClipboard(element) {
        // Clean up previous instance if exists
        if (clipboardInstance) {
            clipboardInstance.destroy();
        }

        clipboardInstance = new ClipboardJS(element);

        clipboardInstance.on('success', () => {
            showTooltip(element, isTouch ? LABELS.mobile.success : LABELS.desktop.success);
            setTimeout(() => {
                hideTooltip();
                if (isTouch) {
                    tooltip.textContent = LABELS.mobile.initial;
                }
            }, RESET_DELAY);
        });

        clipboardInstance.on('error', () => {
            showTooltip(element, isTouch ? LABELS.mobile.error : LABELS.desktop.error);
            setTimeout(() => {
                hideTooltip();
                if (isTouch) {
                    tooltip.textContent = LABELS.mobile.initial;
                }
            }, RESET_DELAY);
        });

        return clipboardInstance;
    }

    copyableElements.forEach(element => {
        element.classList.add('copy-enabled');
        element.setAttribute('data-clipboard-text', element.textContent.trim());

        if (!isTouch) {
            // Desktop behavior
            element.addEventListener('mouseover', () => {
                clearTimeout(hoverTimeout);
                hoverTimeout = setTimeout(() => {
                    showTooltip(element, LABELS.desktop.default);
                }, HOVER_DELAY);
            });

            element.addEventListener('mouseout', () => {
                clearTimeout(hoverTimeout);
                hideTooltip();
            });

            element.addEventListener('click', (e) => {
                clearTimeout(hoverTimeout);
                initClipboard(element).onClick(e);
            });
        } else {
            // Mobile behavior with double-tap
            element.addEventListener('click', (e) => {
                e.preventDefault();
                clearTimeout(tapTimeout);

                if (selectedElement === element) {
                    // Second tap - copy
                    initClipboard(element).onClick(e);
                    selectedElement = null;
                } else {
                    // First tap - show tooltip
                    if (selectedElement) {
                        hideTooltip();
                    }
                    selectedElement = element;
                    showTooltip(element, LABELS.mobile.default);
                    tapTimeout = setTimeout(hideTooltip, TAP_TIMEOUT);
                }
            });
        }
    });

    // Hide tooltip when clicking outside
    if (isTouch) {
        document.addEventListener('click', (e) => {
            if (!e.target.hasAttribute('data-copyable')) {
                hideTooltip();
                selectedElement = null;
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initCopyTooltip();
});
