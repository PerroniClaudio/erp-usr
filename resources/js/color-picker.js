document.addEventListener("DOMContentLoaded", () => {
    const wrappers = document.querySelectorAll(".color-picker-wrapper");

    wrappers.forEach((wrapper) => {
        const colorInput = wrapper.querySelector('input[type="color"]');
        const preview = wrapper.querySelector(".color-preview");
        const hexDisplay = wrapper.querySelector(".color-hex");

        if (!colorInput || !preview || !hexDisplay) return;

        // Initialize
        const updateColor = (color) => {
            preview.style.backgroundColor = color;
            hexDisplay.textContent = color.toUpperCase();
        };

        // Set initial color if value exists, otherwise default to black or input default
        updateColor(colorInput.value);

        // Event listeners
        preview.addEventListener("click", () => {
            colorInput.click();
        });

        colorInput.addEventListener("input", (e) => {
            updateColor(e.target.value);
        });

        hexDisplay.addEventListener("click", () => {
            colorInput.click();
        });
    });
});
