import "./bootstrap";

import Alpine from "alpinejs";
import collapse from "@alpinejs/collapse";
import intersect from "@alpinejs/intersect";
import persist from "@alpinejs/persist";
import focus from "@alpinejs/focus";

// daftar plugin yang dipakai
Alpine.plugin(collapse);
Alpine.plugin(intersect);
Alpine.plugin(persist);
Alpine.plugin(focus);

// optional: expose ke window untuk debug
window.Alpine = Alpine;

Alpine.start();
