<!--
  - Copyright (c) Enalean, 2018. All Rights Reserved.
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
  - along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
  -->

<template>
    <tr v-bind:class="row_classes" v-bind:data-item-id="item.id">
        <td v-bind:colspan="colspan">
            <component
                v-bind:is="cell_title_component_name"
                v-bind:item="item"
                v-bind:style="item_indentation"
                class="document-folder-content-title"
            />
        </td>
        <template v-if="! item.is_uploading">
            <td class="document-tree-cell-owner">
                <user-badge v-bind:user="item.owner"/>
            </td>
            <td class="document-tree-cell-updatedate tlp-tooltip tlp-tooltip-left" v-bind:data-tlp-tooltip="formatted_full_date">
                {{ formatted_date }}
            </td>
        </template>
    </tr>
</template>

<script>
import { mapState } from "vuex";
import UserBadge from "../User/UserBadge.vue";
import { TYPE_FOLDER, TYPE_LINK, TYPE_FILE, TYPE_WIKI } from "../../constants.js";
import moment from "moment";
import phptomoment from "phptomoment";

export default {
    name: "FolderContentRow",
    components: { UserBadge },
    props: {
        item: Object
    },
    computed: {
        ...mapState(["date_time_format", "folded_items_ids"]),
        formatted_date() {
            return moment(this.item.last_update_date).fromNow();
        },
        formatted_full_date() {
            return moment(this.item.last_update_date).format(phptomoment(this.date_time_format));
        },
        is_folded() {
            return this.folded_items_ids.includes(this.item.id);
        },
        item_indentation() {
            if (!this.item.level) {
                return;
            }

            const indentation_size = this.item.level * 23;

            return {
                "padding-left": `${indentation_size}px`
            };
        },
        row_classes() {
            return {
                "document-tree-item-hidden": this.is_folded,
                "document-tree-item-created": this.item.created,
                "document-tree-item-uploading": this.item.is_uploading,
                "document-tree-item-folder": this.item.type === TYPE_FOLDER
            };
        },
        cell_title_component_name() {
            let name = "Document";
            switch (this.item.type) {
                case TYPE_FILE:
                    if (this.item.is_uploading) {
                        name = "FileUploading";
                    } else {
                        name = "File";
                    }

                    break;
                case TYPE_FOLDER:
                case TYPE_LINK:
                case TYPE_WIKI:
                    name = this.item.type;
                    name = name.charAt(0).toUpperCase() + name.slice(1);
                    break;
                default:
                    break;
            }
            return () =>
                import(/* webpackChunkName: "document-cell-title-" */ `./ItemTitle/${name}CellTitle.vue`);
        },
        colspan() {
            return this.item.is_uploading ? 3 : 1;
        }
    },
    mounted() {
        if (!(this.item.created || this.item.is_uploading)) {
            return;
        }

        const magic_number_in_px_to_detect_if_we_partially_show_the_item = 20;
        const position_from_top =
            this.$el.getBoundingClientRect().top +
            magic_number_in_px_to_detect_if_we_partially_show_the_item;
        const viewport_height = window.innerHeight || document.documentElement.clientHeight;
        const is_under_the_fold = position_from_top > viewport_height;

        if (is_under_the_fold) {
            document.dispatchEvent(
                new CustomEvent("item-has-been-created-under-the-fold", {
                    detail: { item: this.item }
                })
            );
        }
    }
};
</script>
