<?php
/**
 * Rasamala Template - Options Bootstrapper & Customizer Loader
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

$rasamala_is_public_customizer = isset($_GET['customize']) && $_GET['customize'] === 'public';
$rasamala_requested_theme = strtolower(trim((string)($_GET['theme'] ?? '')));
$rasamala_current_theme = strtolower(trim((string)($sysconf['template']['theme'] ?? '')));
$rasamala_is_theme_context = $rasamala_requested_theme === ''
  || $rasamala_requested_theme === 'rasamala'
  || $rasamala_current_theme === 'rasamala'
  || basename(dirname(__DIR__)) === 'rasamala'
  || basename(__DIR__) === 'rasamala';

if ($rasamala_is_public_customizer && $rasamala_is_theme_context) {
  $rasamala_csp_nonce = function_exists('themeCspNonce') ? themeCspNonce() : '';
  require_once dirname(__DIR__) . '/tinfo_options_helper.php';
  require_once dirname(__DIR__) . '/tinfo_customizer.php';
  if (function_exists('rasamalaTinfoCustomizeAssets')) {
    echo rasamalaTinfoCustomizeAssets();
  } else {
  echo <<<HTML
<style nonce="{$rasamala_csp_nonce}">
#navbar-menu-builder-container {
    border: 1px solid #E0E0E0;
    background: #FFFFFF;
    padding: 15px;
    border-radius: 4px;
    margin-top: 5px;
}
.menu-builder-row input {
    margin-bottom: 0 !important;
}
.menu-builder-row.is-invalid input {
    border-color: #dc3545;
}
.navbar-menu-builder-help {
    color: #666;
    font-size: 12px;
    margin-bottom: 10px;
}
#topic-items-builder-container {
    border: 1px solid #E0E0E0;
    background: #FFFFFF;
    padding: 15px;
    border-radius: 4px;
    margin-top: 5px;
}
.topic-builder-row input {
    margin-bottom: 0 !important;
}
.topic-builder-row.is-invalid input {
    border-color: #dc3545;
}
.topic-items-builder-help {
    color: #666;
    font-size: 12px;
    margin-bottom: 10px;
}
.rasamala-builder-row {
    gap: 10px;
    display: flex;
    align-items: center;
    margin-bottom: 8px;
}
.rasamala-builder-input-menu-name {
    width: 40%;
    margin-right: 10px;
    display: inline-block;
}
.rasamala-builder-input-menu-url {
    width: 40%;
    margin-right: 10px;
    display: inline-block;
}
.rasamala-builder-input-topic-label {
    width: 30%;
    margin-right: 10px;
    display: inline-block;
}
.rasamala-builder-input-topic-url {
    width: 32%;
    margin-right: 10px;
    display: inline-block;
}
.rasamala-builder-input-topic-icon {
    width: 28%;
    margin-right: 10px;
    display: inline-block;
}
.rasamala-builder-action-btn {
    padding: 4px 12px;
    cursor: pointer;
    display: inline-block;
    font-weight: bold;
    font-size: 14px;
}
</style>
<script nonce="{$rasamala_csp_nonce}">
$(document).ready(function() {
    var textarea = $('textarea[name="classic_navbar_menu"]');
    if (textarea.length) {
        // Hide the default textarea
        textarea.hide();
        
        function isSafeMenuUrl(url) {
            url = String(url || '').trim();
            if (url === '' || /[\x00-\x1f\x7f|;]/.test(url)) return false;
            if (url.charAt(0) === '#') return true;
            if (url.indexOf('//') === 0) return false;
            try {
                var parsed = new URL(url, window.location.origin);
                var rawScheme = url.match(/^([a-z][a-z0-9+.-]*):/i);
                if (rawScheme) {
                    if (['https:', 'mailto:', 'tel:'].indexOf(parsed.protocol) !== -1) return true;
                    return parsed.protocol === 'http:' && window.location.protocol !== 'https:' && parsed.hostname === window.location.hostname;
                }
                return true;
            } catch (e) {
                return false;
            }
        }

        function cleanMenuName(name) {
            return String(name || '').replace(/[|;\r\n]/g, ' ').replace(/\s+/g, ' ').trim();
        }

        function pushItem(items, text, url) {
            text = cleanMenuName(text);
            url = String(url || '').trim();
            if (text !== '' && isSafeMenuUrl(url)) {
                items.push({text: text, url: url});
            }
        }

        function parseMenuValue(rawVal) {
            var items = [];
            rawVal = String(rawVal || '').trim();
            if (rawVal === '') return items;

            var lines = rawVal.split(/[;\n\r]+/);
            for (var i = 0; i < lines.length; i++) {
                var line = lines[i].trim();
                if (line === '') continue;
                var parts = line.split('|');
                if (parts.length >= 2) {
                    pushItem(items, parts[0], parts.slice(1).join('|'));
                }
            }
            return items;
        }

        // Parse the initial value
        var legacyDefaultMenu = 'Home | index.php ; Information | index.php?p=libinfo ; News | index.php?p=news ; Help | index.php?p=help ; Librarian | index.php?p=librarian';
        var rasamalaDefaultMenu = 'Home | index.php ; Information | index.php?p=libinfo ; News | index.php?p=news ; Help | index.php?p=help ; Librarian | index.php?p=librarian ; Staff Area | index.php?p=login';
        var rawVal = textarea.val().trim();
        if (rawVal === legacyDefaultMenu) {
            rawVal = rasamalaDefaultMenu;
            textarea.val(rawVal);
        }
        var items = parseMenuValue(rawVal);
        
        // Create container for builder
        var container = $('<div id="navbar-menu-builder-container" class="mt-2"></div>');
        container.append($('<div class="navbar-menu-builder-help"></div>').text('Format: nama menu dan URL. URL boleh relatif, https, mailto, tel, anchor #, atau http untuk domain yang sama.'));
        var rowsContainer = $('<div id="navbar-menu-rows"></div>');
        container.append(rowsContainer);
        
        var addBtn = $('<button type="button" class="btn btn-success btn-sm mt-2 rasamala-builder-action-btn" id="add-menu-row-btn" title="Tambah Menu">+</button>');
        container.append(addBtn);
        
        textarea.after(container);
        
        function updateTextarea() {
            var itemsList = [];
            rowsContainer.find('.menu-builder-row').each(function() {
                var row = $(this);
                var name = cleanMenuName(row.find('.menu-name-input').val());
                var url = row.find('.menu-url-input').val().trim();
                var isEmpty = name === '' && url === '';
                var isValid = isEmpty || (name !== '' && isSafeMenuUrl(url));
                row.toggleClass('is-invalid', !isValid);
                if (!isEmpty && isValid) {
                    itemsList.push(name + ' | ' + url);
                }
            });
            textarea.val(itemsList.join(' ; '));
        }
        
        function addRow(name, url) {
            name = name || '';
            url = url || '';
            var row = $('<div class="menu-builder-row rasamala-builder-row d-flex align-items-center mb-2"></div>');
            row.append($('<input type="text" class="form-control menu-name-input rasamala-builder-input-menu-name" placeholder="Nama Menu" />').val(cleanMenuName(name)));
            row.append($('<input type="text" class="form-control menu-url-input rasamala-builder-input-menu-url" placeholder="URL" />').val(url));
            row.append($('<button type="button" class="btn btn-danger btn-sm remove-menu-row-btn rasamala-builder-action-btn" title="Hapus">&times;</button>'));
            rowsContainer.append(row);
        }
        
        // Populate initial rows
        if (items.length > 0) {
            for (var i = 0; i < items.length; i++) {
                addRow(items[i].text, items[i].url);
            }
        } else {
            addRow('Home', 'index.php');
        }
        
        // Add row action
        addBtn.click(function() {
            addRow('', '');
            updateTextarea();
        });
        
        // Remove row action
        $(document).on('click', '.remove-menu-row-btn', function() {
            $(this).closest('.menu-builder-row').remove();
            updateTextarea();
        });
        
        // Change input action
        $(document).on('input', '.menu-name-input, .menu-url-input', function() {
            updateTextarea();
        });
    }

    var topicTextarea = $('textarea[name="classic_topic_items"]');
    if (topicTextarea.length) {
        topicTextarea.hide();

        function isSafeTopicUrl(url) {
            url = String(url || '').trim();
            if (url === '' || /[\x00-\x1f\x7f|;]/.test(url)) return false;
            if (url.charAt(0) === '#') return true;
            if (url.indexOf('//') === 0) return false;
            try {
                var parsed = new URL(url, window.location.origin);
                var rawScheme = url.match(/^([a-z][a-z0-9+.-]*):/i);
                if (rawScheme) {
                    return ['http:', 'https:', 'mailto:', 'tel:'].indexOf(parsed.protocol) !== -1;
                }
                return true;
            } catch (e) {
                return false;
            }
        }

        function cleanTopicText(text) {
            return String(text || '').replace(/[|;\r\n]/g, ' ').replace(/\s+/g, ' ').trim();
        }

        function cleanTopicIcon(icon) {
            return String(icon || '').replace(/[|;\r\n]/g, ' ').replace(/\s+/g, ' ').trim();
        }

        function parseTopicValue(rawVal) {
            var items = [];
            rawVal = String(rawVal || '').trim();
            if (rawVal === '') return items;

            var lines = rawVal.split(/[;\n\r]+/);
            for (var i = 0; i < lines.length; i++) {
                var line = lines[i].trim();
                if (line === '') continue;
                var parts = line.split('|');
                if (parts.length >= 2) {
                    var label = cleanTopicText(parts[0]);
                    var url = String(parts[1] || '').trim();
                    var icon = cleanTopicIcon(parts.slice(2).join('|'));
                    if (label !== '' && isSafeTopicUrl(url)) {
                        items.push({label: label, url: url, icon: icon});
                    }
                }
            }
            return items;
        }

        var legacyDefaultTopics = 'Literature | index.php?callnumber=8&search=search | images/8-books.png ; Social Sciences | index.php?callnumber=3&search=search | images/3-diploma.png ; Applied Sciences | index.php?callnumber=6&search=search | images/6-blackboard.png ; Art & Recreation | index.php?callnumber=7&search=search | images/7-quill.png ; see more.. | #exampleModal | images/icon/grid_icon.png';
        var rasamalaDefaultTopics = 'Literature | index.php?callnumber=8&search=search | fas fa-book ; Social Sciences | index.php?callnumber=3&search=search | fas fa-users ; Applied Sciences | index.php?callnumber=6&search=search | fas fa-flask ; Art & Recreation | index.php?callnumber=7&search=search | fas fa-paint-brush ; see more.. | #exampleModal | fas fa-th-large';
        var rawTopicVal = topicTextarea.val().trim();
        if (rawTopicVal === legacyDefaultTopics) {
            rawTopicVal = rasamalaDefaultTopics;
            topicTextarea.val(rawTopicVal);
        }
        var topicRows = parseTopicValue(rawTopicVal);
        var topicContainer = $('<div id="topic-items-builder-container" class="mt-2"></div>');
        topicContainer.append($('<div class="topic-items-builder-help"></div>').text('Format: nama topic, URL, dan ikon. Gunakan Font Awesome bawaan tema, contoh: fas fa-book, fas fa-users, fas fa-flask. Path gambar tema tetap didukung bila diperlukan.'));
        var topicRowsContainer = $('<div id="topic-items-rows"></div>');
        topicContainer.append(topicRowsContainer);
        var addTopicBtn = $('<button type="button" class="btn btn-success btn-sm mt-2 rasamala-builder-action-btn" id="add-topic-row-btn" title="Tambah Topic">+</button>');
        topicContainer.append(addTopicBtn);
        topicTextarea.after(topicContainer);

        function updateTopicTextarea() {
            var itemsList = [];
            topicRowsContainer.find('.topic-builder-row').each(function() {
                var row = $(this);
                var label = cleanTopicText(row.find('.topic-label-input').val());
                var url = row.find('.topic-url-input').val().trim();
                var icon = cleanTopicIcon(row.find('.topic-icon-input').val());
                var isEmpty = label === '' && url === '' && icon === '';
                var isValid = isEmpty || (label !== '' && isSafeTopicUrl(url));
                row.toggleClass('is-invalid', !isValid);
                if (!isEmpty && isValid) {
                    itemsList.push(label + ' | ' + url + ' | ' + icon);
                }
            });
            topicTextarea.val(itemsList.join(' ; '));
        }

        function addTopicRow(label, url, icon) {
            var row = $('<div class="topic-builder-row rasamala-builder-row d-flex align-items-center mb-2"></div>');
            row.append($('<input type="text" class="form-control topic-label-input rasamala-builder-input-topic-label" placeholder="Nama Topic" />').val(cleanTopicText(label)));
            row.append($('<input type="text" class="form-control topic-url-input rasamala-builder-input-topic-url" placeholder="URL" />').val(url || ''));
            row.append($('<input type="text" class="form-control topic-icon-input rasamala-builder-input-topic-icon" placeholder="Ikon" />').val(cleanTopicIcon(icon)));
            row.append($('<button type="button" class="btn btn-danger btn-sm remove-topic-row-btn rasamala-builder-action-btn" title="Hapus">&times;</button>'));
            topicRowsContainer.append(row);
        }

        if (topicRows.length > 0) {
            for (var j = 0; j < topicRows.length; j++) {
                addTopicRow(topicRows[j].label, topicRows[j].url, topicRows[j].icon);
            }
        } else {
            addTopicRow('Literature', 'index.php?callnumber=8&search=search', 'fas fa-book');
        }

        addTopicBtn.click(function() {
            addTopicRow('', '', '');
            updateTopicTextarea();
        });

        $(document).on('click', '.remove-topic-row-btn', function() {
            $(this).closest('.topic-builder-row').remove();
            updateTopicTextarea();
        });

        $(document).on('input', '.topic-label-input, .topic-url-input, .topic-icon-input', function() {
            updateTopicTextarea();
        });
    }
});
</script>
HTML;
  }
}
