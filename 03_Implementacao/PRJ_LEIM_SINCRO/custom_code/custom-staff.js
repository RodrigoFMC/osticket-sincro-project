<?php
// Converts the table prefix to JSON format for use in JavaScript
$tablePrefixJs = json_encode(TABLE_PREFIX);

// Function to fetch distinct values from a specific column in a table
function getFieldValuesFromDatabase($columnName, $tableName) {
    $prefixedTableName = TABLE_PREFIX . $tableName;
    $query = "SELECT DISTINCT $columnName FROM $prefixedTableName ORDER BY $columnName";
    $result = db_query($query);
    $values = [];
    while ($row = db_fetch_array($result)) {
        $values[] = htmlspecialchars($row[$columnName], ENT_QUOTES, 'UTF-8');
    }
    return $values;
}
// Converts the district data to JSON format for use in JavaScript
$distritosJs = json_encode(getFieldValuesFromDatabase('district', 'sincro_cabinet'));
?>

$(document).ready(function() {
    console.log("DOM fully loaded and parsed");
    var distritosData = <?php echo $distritosJs; ?>;
    var tablePrefix = <?php echo $tablePrefixJs; ?>;
    var isFetching = false;

    // Function to create a <select> element with the provided options
    function createSelectBox(options, name, id, placeholder) {
        const select = document.createElement('select');
        select.name = name;
        select.id = id;

        const blankOption = document.createElement('option');
        blankOption.value = '';
        blankOption.textContent = placeholder;
        select.appendChild(blankOption);

        options.forEach(function(value) {
            const option = document.createElement('option');
            option.value = option.textContent = value;
            select.appendChild(option);
        });
        return select;
    }

    // Function to update a <select> element with new options
    function updateOptions(select, options, placeholder) {
        select.innerHTML = '';
        const blankOption = document.createElement('option');
        blankOption.value = '';
        blankOption.textContent = placeholder;
        select.appendChild(blankOption);

        options.forEach(function(value) {
            const option = document.createElement('option');
            option.value = option.textContent = value;
            select.appendChild(option);
        });
    }

     // Function to fetch and update options dynamically using AJAX
    function fetchAndUpdateOptions(selectId, tableName, columnName, filterValue) {
        if (isFetching) return;
        isFetching = true;

        var url = 'ajax.php/ajax-options/getOptions/' + tablePrefix + tableName + '/' + columnName + '/' + filterValue;


        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                const select = document.getElementById(selectId);
                const placeholder = 'Selecione uma opção';
                updateOptions(select, data, placeholder);
                isFetching = false;
            },
            error: function(xhr, status, error) {
                console.error("Plugin AJAX Error: ", status, error);
                isFetching = false;
            }
        });
    }

    // Function to filter cabinet options based on selected district, address, and apAf
    function filterCabines(district, address, apAf) {
        const cabinesSelect = document.getElementById('_lista_cabines');
        const options = cabinesSelect.querySelectorAll('option');

        options.forEach(option => {
            if (option.value === '') {
                option.textContent = '— lista_cabines —';
                return;
            }
            const [
                id, , , , , , , optionAddress, optionDistrict, , , optionApAf
            ] = option.textContent.split(';');
            let match = true;

            if (district && optionDistrict !== district) {
                match = false;
            }
            if (address && optionAddress !== address) {
                match = false;
            }
            if (apAf && !optionApAf.includes(apAf)) {
                match = false;
            }

            option.style.display = match ? '' : 'none';
        });

        cabinesSelect.value = '';
    }

    // Function to clear options from a <select> element
    function clearSelectBox(select) {
        if (select) {
            select.innerHTML = '';
            const blankOption = document.createElement('option');
            blankOption.value = '';
            blankOption.textContent = 'Selecione uma opção';
            select.appendChild(blankOption);
        }
    }

     // Function to insert and manage the filtering select boxes
    function insertSelectBoxes() {
        const referenceNode = document.getElementById('_lista_cabines');
        if (!referenceNode) return;

        let distritosSelect, enderecosSelect, apAfSelect;

        if (!document.getElementById('lista_distritos')) {
            distritosSelect = createSelectBox(distritosData, 'lista_distritos', 'lista_distritos', 'Selecione um distrito');
            referenceNode.parentNode.insertBefore(distritosSelect, referenceNode);
        } else {
            distritosSelect = document.getElementById('lista_distritos');
        }

        if (!document.getElementById('lista_enderecos')) {
            enderecosSelect = createSelectBox([], 'lista_enderecos', 'lista_enderecos', 'Selecione um endereço');
            referenceNode.parentNode.insertBefore(enderecosSelect, referenceNode);
        } else {
            enderecosSelect = document.getElementById('lista_enderecos');
        }

        if (!document.getElementById('lista_ap_af')) {
            apAfSelect = createSelectBox([], 'lista_ap_af', 'lista_ap_af', 'Selecione uma opção');
            referenceNode.parentNode.insertBefore(apAfSelect, referenceNode);
        } else {
            apAfSelect = document.getElementById('lista_ap_af');
        }

        distritosSelect.addEventListener('change', function() {
            const distritoValue = this.value;
            if (distritoValue) {
                fetchAndUpdateOptions('lista_enderecos', 'sincro_cabinet', 'address', distritoValue);
                fetchAndUpdateOptions('lista_ap_af', 'sincro_cabinet', 'ap_af', distritoValue);
            } else {
                clearSelectBox(enderecosSelect);
                clearSelectBox(apAfSelect);
            }
            filterCabines(distritoValue, enderecosSelect.value, apAfSelect.value);
        });

        enderecosSelect.addEventListener('change', function() {
            const enderecoValue = this.value;
            if (enderecoValue) {
                fetchAndUpdateOptions('lista_ap_af', 'sincro_cabinet', 'ap_af', distritosSelect.value);
            } else {
                clearSelectBox(apAfSelect);
            }
            filterCabines(distritosSelect.value, enderecoValue, apAfSelect.value);
        });

        apAfSelect.addEventListener('change', function() {
            filterCabines(distritosSelect.value, enderecosSelect.value, this.value);
        });
    }

     // Uses MutationObserver to monitor changes in the DOM and ensures the select boxes are correctly inserted
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length || mutation.removedNodes.length) {
                observer.disconnect();
                insertSelectBoxes();
                observer.observe(targetNode, { childList: true, subtree: true });
            }
        });
    });

    var targetNode = document.getElementById('dynamic-form');
    if (targetNode) {
        observer.observe(targetNode, { childList: true, subtree: true });
    }

    insertSelectBoxes(); // Insert select boxes initially
});