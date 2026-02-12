/*
 * @package    plg_system_adminpath
 * @version    1.0.0
 * @author     Ilya A.Zhulin <ilya.zhulin@hotmail.com>
 * @copyright  ©Ilya A.Zhulin, 2026
 * @license    GNU General Public License version 2 or later;
 *
 * The last change: 12.02.2026, 15:59
 */

/**
 * AdminPath Plugin - Dynamic URL Display with Session Check
 *
 * @package     Joomla.Plugin
 * @subpackage  System.adminpath
 * @since       1.0.0
 */

(function () {
    'use strict';

    let hasSessionAccess = false;
    let checkInterval = null;

    function initAdminPath() {
        const secretPathField = document.getElementById('jform_params_secret_path');
        const blockDirectYes = document.getElementById('jform_params_block_direct1'); // "Yes" radio button
        const blockDirectNo = document.getElementById('jform_params_block_direct0');  // "No" radio button
        const urlDisplayContainer = secretPathField?.closest('.control-group')?.nextElementSibling;
        const urlDisplayField = urlDisplayContainer?.querySelector('.alert');

        if (!secretPathField || !blockDirectYes || !blockDirectNo || !urlDisplayField) {
            return;
        }

        const siteUrl = window.location.origin + '/administrator';
        const warningContainer = createWarningContainer();

        function createWarningContainer() {
            const container = document.createElement('div');
            container.id = 'adminpath_warning';
            container.style.cssText = 'display:none;margin-top:12px;padding:12px;background:#fff3cd;border-left:4px solid #ffc107;color:#856404;font-size:14px;border-radius:4px;';
            container.innerHTML = '';
            urlDisplayField.parentNode.insertBefore(container, urlDisplayField.nextSibling);
            return container;
        }

        function updateUrlDisplay() {
            const secretParam = secretPathField.value.trim();
            let fullUrl = siteUrl;

            if (secretParam) {
                fullUrl += '?' + encodeURIComponent(secretParam);
            } else {
                fullUrl += '?[secret-parameter]';
            }

            const labelText = Joomla.Text._('PLG_SYSTEM_ADMINPATH_YOUR_ADMIN_URL');
            const copyText = Joomla.Text._('PLG_SYSTEM_ADMINPATH_COPY_LINK');
            console.log(labelText);
            urlDisplayField.innerHTML = `
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div>
                        <strong>${labelText}:</strong>
                    </div>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <a href="${fullUrl}" target="_blank" style="flex:1;text-decoration:none;color:#007bff;font-weight:500;word-break:break-all;" id="adminpath_link">
                            ${fullUrl}
                        </a>
                        <button type="button" 
                                class="btn btn-sm btn-secondary" 
                                onclick="copyAdminUrl('${fullUrl}', event)"
                                title="${copyText}"
                                style="white-space:nowrap;">
                            ${copyText}
                        </button>
                    </div>
                </div>
            `;
        }

        function checkSessionStatus() {
            Joomla.request({
                url: 'index.php?option=com_ajax&plugin=adminpath&group=system&format=json',
                method: 'POST',
                headers: {
                    'Cache-Control': 'no-cache',
                    'Content-Type': 'application/json'
                }, data: new URLSearchParams({
                    option: 'com_ajax',
                    plugin: 'adminpath',
                    group: 'system',
                    format: 'json'
                }).toString(),
                onSuccess: (response) => {
                    let data = JSON.parse(response);
                    if (data.success) {
                        hasSessionAccess = data.data;
                        updateWarningDisplay();
                    }
                }
            });
        }

        function updateWarningDisplay() {
            if (blockDirectYes.checked && !hasSessionAccess) {
                const warningText = typeof Joomla !== 'undefined' && Joomla.Text
                    ? Joomla.Text._('PLG_SYSTEM_ADMINPATH_WARNING_BLOCK', '<strong style="color:#dc3545;">⚠️ WARNING:</strong> Direct access is blocked but you don\'t have session access! <strong>Click the link above</strong> to open admin panel in new tab, then return and save.')
                    : '<strong style="color:#dc3545;">⚠️ WARNING:</strong> Direct access is blocked but you don\'t have session access! <strong>Click the link above</strong> to open admin panel in new tab, then return and save.';

                warningContainer.innerHTML = warningText;
                warningContainer.style.display = 'block';

                // Make it pulse
                let pulseCount = 0;
                const pulseInterval = setInterval(() => {
                    warningContainer.style.borderColor = pulseCount % 2 === 0 ? '#dc3545' : '#ffc107';
                    pulseCount++;
                    if (pulseCount > 6) {
                        clearInterval(pulseInterval);
                        warningContainer.style.borderColor = '#ffc107';
                    }
                }, 500);
            } else {
                warningContainer.style.display = 'none';
            }
        }

        function startChecking() {
            // Stop any existing interval
            if (checkInterval) {
                clearInterval(checkInterval);
            }

            // Check immediately
            checkSessionStatus();

            // Then check every 3 seconds
            checkInterval = setInterval(checkSessionStatus, 3000);
        }

        function stopChecking() {
            if (checkInterval) {
                clearInterval(checkInterval);
                checkInterval = null;
            }
            warningContainer.style.display = 'none';
        }

        // Start checking when "Yes" is clicked
        blockDirectYes.addEventListener('click', function () {
            startChecking();
        });

        // Stop checking when "No" is clicked
        blockDirectNo.addEventListener('click', function () {
            stopChecking();
        });

        // Initial state
        if (blockDirectYes.checked) {
            startChecking();
        }

        // Update URL display
        updateUrlDisplay();

        // Update on secret path input
        let timeout;
        secretPathField.addEventListener('input', function () {
            clearTimeout(timeout);
            timeout = setTimeout(updateUrlDisplay, 300);
        });
    }

    window.copyAdminUrl = function (url, event) {
        navigator.clipboard.writeText(url).then(function () {
            const originalText = event.currentTarget.innerHTML;
            const successText = typeof Joomla !== 'undefined' && Joomla.Text
                ? Joomla.Text._('PLG_SYSTEM_ADMINPATH_COPIED', 'Copied!')
                : 'Copied!';

            event.currentTarget.innerHTML = successText;
            event.currentTarget.classList.add('btn-success');

            setTimeout(function () {
                event.currentTarget.innerHTML = originalText;
                event.currentTarget.classList.remove('btn-success');
            }, 2000);
        }).catch(function (err) {
            console.error('Could not copy text: ', err);
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAdminPath);
    } else {
        initAdminPath();
    }
})();
