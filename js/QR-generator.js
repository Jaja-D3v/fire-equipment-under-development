
// ==============================
// SEARCH + PAGINATION
// ==============================

const qrSearch =
    document.getElementById('qrSearch');

const tbody =
    document.querySelector('table tbody');

const rowsPerPage = 10;

let currentPage = 1;


// ==============================
// GET DATA ROWS
// ==============================

function getDataRows() {

    return Array.from(
        tbody.querySelectorAll(
            'tr:not(#qrNoResults)'
        )
    ).filter(row => {

        return row.querySelector(
            '.extinguisher-checkbox'
        );

    });
}


// ==============================
// GET FILTERED ROWS
// ==============================

function getFilteredRows() {

    const searchValue =
        qrSearch
            ? qrSearch.value
                .trim()
                .toLowerCase()
            : '';

    const rows =
        getDataRows();

    return rows.filter(row => {

        const checkbox =
            row.querySelector(
                '.extinguisher-checkbox'
            );

        if (!checkbox) {
            return false;
        }

        const code =
            checkbox.value
                .toLowerCase();

        const location =
            row.cells[3]
                ? row.cells[3]
                    .textContent
                    .trim()
                    .toLowerCase()
                : '';

        return (
            searchValue === '' ||
            code.includes(searchValue) ||
            location.includes(searchValue)
        );
    });
}


// ==============================
// DISPLAY ROWS
// ==============================

function displayRows() {

    const rows =
        getDataRows();

    const filteredRows =
        getFilteredRows();

    const totalPages =
        Math.ceil(
            filteredRows.length /
            rowsPerPage
        );


    // Make sure current page
    // is still valid

    if (
        totalPages > 0 &&
        currentPage > totalPages
    ) {

        currentPage =
            totalPages;
    }


    if (filteredRows.length === 0) {

        currentPage = 1;
    }


    // ==============================
    // HIDE ALL ROWS
    // ==============================

    rows.forEach(row => {

        row.classList.add(
            'd-none'
        );

    });


    // ==============================
    // SHOW CURRENT PAGE
    // ==============================

    const start =
        (currentPage - 1) *
        rowsPerPage;

    const end =
        start + rowsPerPage;


    filteredRows
        .slice(start, end)
        .forEach(row => {

            row.classList.remove(
                'd-none'
            );

        });


    // ==============================
    // NO RESULTS
    // ==============================

    let noResultsRow =
        document.getElementById(
            'qrNoResults'
        );


    if (
        filteredRows.length === 0 &&
        qrSearch &&
        qrSearch.value.trim() !== ''
    ) {

        if (!noResultsRow) {

            noResultsRow =
                document.createElement(
                    'tr'
                );

            noResultsRow.id =
                'qrNoResults';

            noResultsRow.innerHTML = `
                    <td colspan="6"
                        class="text-center py-5">

                        <div
                            class="bg-light rounded-circle
                                   d-flex align-items-center
                                   justify-content-center
                                   mx-auto mb-3"
                            style="width:60px;height:60px;">

                            <i
                                class="bi bi-search
                                       text-body-secondary fs-4">
                            </i>

                        </div>

                        <div
                            class="fw-semibold text-dark">

                            No results found

                        </div>

                        <div
                            class="small text-body-secondary mt-1">

                            No fire extinguisher
                            matches your search.

                        </div>

                    </td>
                `;

            tbody.appendChild(
                noResultsRow
            );
        }

    } else {

        if (noResultsRow) {

            noResultsRow.remove();
        }
    }


    // ==============================
    // PAGINATION
    // ==============================

    renderPagination(
        totalPages
    );


    // ==============================
    // UPDATE SELECTION
    // ==============================

    updateSelection();
}


// ==============================
// PAGINATION
// ==============================

function renderPagination(
    totalPages
) {

    let paginationContainer =
        document.getElementById(
            'qrPagination'
        );


    // ==============================
    // CREATE PAGINATION CONTAINER
    // ==============================

    if (!paginationContainer) {

        paginationContainer =
            document.createElement(
                'div'
            );

        paginationContainer.id =
            'qrPagination';

        paginationContainer.className =
            'card-footer bg-white border-top';


        const card =
            document.querySelector(
                '.qr-main-card'
            );


        if (card) {

            card.appendChild(
                paginationContainer
            );
        }
    }


    paginationContainer.innerHTML =
        '';


    // ==============================
    // FILTERED DATA
    // ==============================

    const filteredRows =
        getFilteredRows();

    const totalItems =
        filteredRows.length;


    // ==============================
    // ITEM RANGE
    // ==============================

    let startItem = 0;

    let endItem = 0;


    if (totalItems > 0) {

        startItem =
            (
                (currentPage - 1) *
                rowsPerPage
            ) + 1;


        endItem =
            Math.min(
                currentPage *
                rowsPerPage,
                totalItems
            );
    }


    // ==============================
    // FOOTER WRAPPER
    // ==============================

    const footerWrapper =
        document.createElement(
            'div'
        );

    footerWrapper.className =
        'd-flex flex-column ' +
        'flex-sm-row ' +
        'justify-content-between ' +
        'align-items-center gap-2';


    // ==============================
    // ITEM COUNT
    // ==============================

    const itemCount =
        document.createElement(
            'small'
        );

    itemCount.className =
        'text-body-secondary';


    if (totalItems > 0) {

        itemCount.textContent =
            `Showing ${startItem}–${endItem} ` +
            `of ${totalItems} items`;

    } else {

        itemCount.textContent =
            'Showing 0 items';
    }


    // ==============================
    // PAGINATION WRAPPER
    // ==============================

    const paginationWrapper =
        document.createElement(
            'div'
        );


    if (totalPages > 1) {

        const nav =
            document.createElement(
                'nav'
            );


        const ul =
            document.createElement(
                'ul'
            );

        ul.className =
            'pagination pagination-sm mb-0';


        // ==============================
        // PREVIOUS
        // ==============================

        const prevLi =
            document.createElement(
                'li'
            );

        prevLi.className =
            'page-item' +
            (
                currentPage === 1
                    ? ' disabled'
                    : ''
            );


        const prevButton =
            document.createElement(
                'button'
            );

        prevButton.type =
            'button';

        prevButton.className =
            'page-link';

        prevButton.innerHTML =
            '<i class="bi bi-chevron-left"></i>';


        prevButton.addEventListener(
            'click',
            function () {

                if (
                    currentPage > 1
                ) {

                    currentPage--;

                    displayRows();
                }
            }
        );


        prevLi.appendChild(
            prevButton
        );

        ul.appendChild(
            prevLi
        );


        // ==============================
        // PAGE NUMBERS
        // ==============================

        let pages = [];


        if (totalPages <= 5) {

            // ==============================
            // 5 OR FEWER PAGES
            // ==============================

            for (
                let page = 1;
                page <= totalPages;
                page++
            ) {

                pages.push(page);
            }


        } else {

            // ==============================
            // BEGINNING
            // ==============================

            if (
                currentPage <= 3
            ) {

                pages = [
                    1,
                    2,
                    3,
                    4,
                    totalPages
                ];


                // ==============================
                // END
                // ==============================

            } else if (
                currentPage >=
                totalPages - 2
            ) {

                pages = [
                    1,
                    totalPages - 3,
                    totalPages - 2,
                    totalPages - 1,
                    totalPages
                ];


                // ==============================
                // MIDDLE
                // ==============================

            } else {

                pages = [
                    1,
                    currentPage - 1,
                    currentPage,
                    currentPage + 1,
                    totalPages
                ];
            }
        }


        // ==============================
        // RENDER PAGE NUMBERS
        // ==============================

        let previousPage = null;


        pages.forEach(
            page => {

                // ==============================
                // ADD DOTS
                // ==============================

                if (
                    previousPage !== null &&
                    page - previousPage > 1
                ) {

                    const dotsLi =
                        document.createElement(
                            'li'
                        );

                    dotsLi.className = 'page-item disabled';


                    dotsLi.innerHTML = `
                            <span class="page-link">
                                ...
                            </span>
                        `;


                    ul.appendChild(
                        dotsLi
                    );
                }


                // ==============================
                // PAGE BUTTON
                // ==============================

                const li = document.createElement( 'li' );
                li.className =
                    'page-item' +
                    (
                        page === currentPage
                            ? ' active'
                            : ''
                    );


                const button = document.createElement( 'button' );
                button.type = 'button';
                button.className = 'page-link';
                button.textContent = page;


                button.addEventListener(
                    'click',
                    function () {

                        currentPage =
                            page;

                        displayRows();
                    }
                );


                li.appendChild(
                    button
                );


                ul.appendChild(
                    li
                );


                previousPage =
                    page;
            }
        );


        // ==============================
        // NEXT
        // ==============================

        const nextLi = document.createElement( 'li' );
        nextLi.className = 'page-item' + ( currentPage === totalPages ? ' disabled' : '' );


        const nextButton =
            document.createElement(
                'button'
            );


        nextButton.type = 'button';
        nextButton.className = 'page-link';
        nextButton.innerHTML = '<i class="bi bi-chevron-right"></i>';

        nextButton.addEventListener(
            'click',
            function () {

                if (
                    currentPage <
                    totalPages
                ) {

                    currentPage++;

                    displayRows();
                }
            }
        );


        nextLi.appendChild( nextButton );
        ul.appendChild( nextLi );


        // ==============================
        // APPEND PAGINATION
        // ==============================

        nav.appendChild( ul );
        paginationWrapper.appendChild( nav );
    }


    // ==============================
    // FOOTER
    // ==============================

    footerWrapper.appendChild( itemCount );
    footerWrapper.appendChild( paginationWrapper );
    paginationContainer.appendChild( footerWrapper );
}


// ==============================
// SEARCH
// ==============================

if (qrSearch) {

    qrSearch.addEventListener(
        'input',
        function () {

            // Reset to page 1
            currentPage = 1;


            // Uncheck hidden rows

            getDataRows()
                .forEach(
                    row => {

                        if (
                            row.classList.contains(
                                'd-none'
                            )
                        ) {

                            const checkbox = row.querySelector( '.extinguisher-checkbox' );
                            if (checkbox) {

                                checkbox.checked =
                                    false;
                            }
                        }
                    }
                );


            displayRows();
        }
    );
}


// ==============================
// SELECT ALL
// ==============================

function toggleSelectAll(
    source
) {

    const filteredRows = getFilteredRows();
    const start = (currentPage - 1) * rowsPerPage;
    const currentPageRows = filteredRows.slice( start, start + rowsPerPage );

    currentPageRows.forEach(
        row => {

            const checkbox = row.querySelector( '.extinguisher-checkbox' );


            if (checkbox) {

                checkbox.checked = source.checked;
            }
        }
    );


    updateSelection();
}


// ==============================
// UPDATE SELECTION
// ==============================

function updateSelection() {

    const checkboxes = document.querySelectorAll( '.extinguisher-checkbox' );
    const selected = document.querySelectorAll( '.extinguisher-checkbox:checked' ).length;

    const visibleCheckboxes =
        Array.from(
            checkboxes
        ).filter(
            cb => {

                const row =
                    cb.closest('tr');


                return (
                    row &&
                    !row.classList.contains(
                        'd-none'
                    )
                );
            }
        );


    const visibleSelected = visibleCheckboxes.filter( cb => cb.checked ).length;
    const selectedCount = document.getElementById( 'selectedCount' );
    const selectionFooter = document.getElementById( 'selectionFooter' );
    const selectAll = document.getElementById( 'selectAll' );

    // SELECTED COUNT
    if (selectedCount) {

        selectedCount.textContent = selected;
    }

    // SELECTION FOOTER
    if (selectionFooter) {

        if (selected > 0) {

            selectionFooter.classList.remove( 'd-none' );

        } else {

            selectionFooter.classList.add( 'd-none' );
        }
    }


    // ==============================
    // SELECT ALL STATE
    // ==============================

    if (selectAll) {

        if (
            visibleCheckboxes.length > 0 &&
            visibleSelected ===
            visibleCheckboxes.length
        ) {

            selectAll.checked = true;
            selectAll.indeterminate = false;


        } else if (

            visibleSelected > 0

        ) {

            selectAll.checked = false;
            selectAll.indeterminate = true;


        } else {

            selectAll.checked = false;
            selectAll.indeterminate = false;
        }
    }
}


// ==============================
// CLEAR SELECTION
// ==============================

function clearSelection() {

    document
        .querySelectorAll(
            '.extinguisher-checkbox'
        )
        .forEach(
            cb => {

                cb.checked =
                    false;
            }
        );


    const selectAll = document.getElementById( 'selectAll' );


    if (selectAll) {

        selectAll.checked = false;
        selectAll.indeterminate = false;
    }

    updateSelection();
}


// ==============================
// PRINT SELECTED
// ==============================

function printSelected() {

    const selected = [

        ...document.querySelectorAll(
            '.extinguisher-checkbox:checked'
        )

    ].map(
        cb => cb.value
    );


    if (!selected.length) {

        alert(
            'Please select at least one fire extinguisher.'
        );

        return;
    }


    const params = new URLSearchParams();
    params.set('codes', selected.join(','));
    params.set('branch', window.qrPrintBranch || '');
    window.open('helpers/print-qr.php?' + params.toString(), '_blank');
}

// ==============================
// INITIAL LOAD
// ==============================
document.addEventListener(
    'DOMContentLoaded',
    function () {

        displayRows();

    }
);
