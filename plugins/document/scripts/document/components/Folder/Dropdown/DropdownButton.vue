<!--
  - Copyright (c) Enalean, 2019. All Rights Reserved.
  -
  - This file is a part of Tuleap.
  -
  - Tuleap is free software; you can redistribute it and/or modify
  - it under the terms of the GNU General Public License as published by
  - the Free Software Foundation; either version 2 of the License, or
  - (at your option) any later version.
  -
  - Tuleap is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU General Public License for more details.
  -
  - You should have received a copy of the GNU General Public License
  - along with Tuleap. If not, see http://www.gnu.org/licenses/.
  -
  -->

<template>
    <div>
        <button class="tlp-button-primary tlp-dropdown-split-button-caret"
                v-bind:class="{'tlp-button-large': is_in_large_mode}"
                ref="dropdownButton"
                type="button"
        >
            <i class="fa fa-caret-down"></i>
        </button>
        <dropdown-menu v-bind:is_in_large_mode="is_in_large_mode"/>
    </div>
</template>

<script>
import DropdownMenu from "./DropdownMenu.vue";
import { dropdown as createDropdown } from "tlp";

export default {
    name: "DropdownButton",
    components: { DropdownMenu },
    props: {
        is_in_large_mode: Boolean
    },
    mounted() {
        const dropdown = createDropdown(this.$refs.dropdownButton);
        dropdown.hide();
    }
};
</script>
