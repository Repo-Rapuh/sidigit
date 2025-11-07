import "./bootstrap";

import collapse from "@alpinejs/collapse";
import intersect from "@alpinejs/intersect";
// import persist from "@alpinejs/persist";
import focus from "@alpinejs/focus";

const registerPlugins = (Alpine) => {
	Alpine.plugin(collapse);
	Alpine.plugin(intersect);
	// Alpine.plugin(persist);
	Alpine.plugin(focus);
};

if (!window.Alpine) {
	import("alpinejs").then(({default: Alpine}) => {
		registerPlugins(Alpine);
		window.Alpine = Alpine;
		Alpine.start();
	});
} else {
	registerPlugins(window.Alpine);
}

document.addEventListener("alpine:init", () => {
	if (window.Alpine) {
		registerPlugins(window.Alpine);
	}
});
