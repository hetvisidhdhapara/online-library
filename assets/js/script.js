
// HOME PAGE SEARCH AND FILTER
const homeSearch = document.querySelector("#bookSearch");
const homeCategory = document.querySelector("#categoryFilter");
const homeSearchBtn = document.querySelector("#searchBtn");

if (homeSearch && homeCategory) {

    const homeCards = document.querySelectorAll(".book-card");
    const homeNoResults = document.querySelector("#noBooksMessage");

    function filterHomeBooks() {
        const searchText = homeSearch.value.toLowerCase().trim();
        const selectedCategory = homeCategory.value.toLowerCase();

        let visibleBooks = 0;

        homeCards.forEach(function(book) {
            const titleElement = book.querySelector(".book-title");
            const title = titleElement
                ? titleElement.textContent.toLowerCase()
                : book.dataset.title || "";

            const category = (book.dataset.category || "").toLowerCase();

            const matchesSearch = title.includes(searchText);

            const matchesCategory =
                selectedCategory === "" ||
                selectedCategory === "all" ||
                category === selectedCategory;

            if (matchesSearch && matchesCategory) {
                book.style.display = "";
                visibleBooks++;
            } else {
                book.style.display = "none";
            }
        });

        if (homeNoResults) {
            homeNoResults.style.display =
                visibleBooks === 0 ? "block" : "none";
        }
    }

    homeSearch.addEventListener("input", filterHomeBooks);
    homeCategory.addEventListener("change", filterHomeBooks);

    if (homeSearchBtn) {
        homeSearchBtn.addEventListener("click", filterHomeBooks);
    }
}


// STUDENT PAGE SEARCH AND FILTER
const studentSearch = document.querySelector("#bookSearch");
const studentCategory = document.querySelector("#categoryFilter");

if (studentSearch && studentCategory) {

    const studentCards = document.querySelectorAll(".book-card");
    const noResults = document.querySelector("#noResults");

    function filterStudentBooks() {
        const searchValue = studentSearch.value.toLowerCase().trim();
        const selectedCategory = studentCategory.value.toLowerCase();

        let visibleBooks = 0;

        studentCards.forEach(function(card) {
            const title = (card.dataset.title || "").toLowerCase();
            const author = (card.dataset.author || "").toLowerCase();
            const category = (card.dataset.category || "").toLowerCase();

            const matchesSearch =
                title.includes(searchValue) ||
                author.includes(searchValue);

            const matchesCategory =
                selectedCategory === "" ||
                category === selectedCategory;

            if (matchesSearch && matchesCategory) {
                card.style.display = "";
                visibleBooks++;
            } else {
                card.style.display = "none";
            }
        });

        if (noResults) {
            noResults.style.display =
                visibleBooks === 0 ? "block" : "none";
        }
    }

    studentSearch.addEventListener("input", filterStudentBooks);
    studentCategory.addEventListener("change", filterStudentBooks);
}
