class IqitCustomWidget {
    constructor(selector) {
        this.selector = selector;
    }

    changeBackground() {
        document.addEventListener("DOMContentLoaded", () => {
            const customWidget = document.querySelector(this.selector);
            if (customWidget) {
                customWidget.style.backgroundColor = "black";
            }
        });
    }
}

const widgetBackgroundChanger = new IqitCustomWidget('.elementor-widget-widget-custom');
widgetBackgroundChanger.changeBackground();