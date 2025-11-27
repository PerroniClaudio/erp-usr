document.addEventListener("DOMContentLoaded", () => {
    const nameInput = document.querySelector('input[name="name"]');
    const acronymInput = document.querySelector('input[name="acronym"]');

    if (!nameInput || !acronymInput) return;

    nameInput.addEventListener("input", function () {
        const value = this.value.trim();
        if (!value) {
            acronymInput.value = "";
            return;
        }

        const words = value.split(/\s+/);
        let acronym = "";

        if (words.length === 1) {
            acronym = words[0].substring(0, 3);
        } else {
            words.forEach((word) => {
                if (word.length > 0) {
                    acronym += word[0];
                }
            });
        }

        // Limit to 4 chars as per the input maxlength
        acronymInput.value = acronym.toUpperCase().substring(0, 4);
    });
});
