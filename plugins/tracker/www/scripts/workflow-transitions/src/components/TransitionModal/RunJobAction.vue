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
  - along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
  -
  -->

<template>
    <div class="tlp-form-element">
        <label v-bind:for="job_url_input_id" class="tlp-label" v-translate>Job url</label>
        <input
            v-bind:id="job_url_input_id"
            type="text"
            class="tlp-input"
            placeholder="https://www.example.com"
            v-model="job_url"
            data-test-type="job-url"
            required
            v-bind:disabled="is_modal_save_running"
        >
        <p class="tlp-text-info" v-translate>
            Tuleap will automatically pass the following parameters to the job:
        </p>
        <ul class="tlp-text-info">
            <li v-translate>
                userId: identifier of Tuleap user who made the transition (integer)
            </li>
            <li v-translate="{ project_id: current_tracker.project.id }">
                projectId: identifier of the current project (ie. %{ project_id }) (integer)
            </li>
            <li v-translate="{ tracker_id: current_tracker.id}">
                trackerId: identifier of the current tracker (ie. %{ tracker_id }) (integer)
            </li>
            <li v-translate>
                artifactId: identifier of the artifact where the transition happens (integer)
            </li>
            <li v-translate="{ transition_id: current_transition.id }">
                triggerFieldValue: value of current transition target (ie. %{ transition_id }) (string)
            </li>
        </ul>
    </div>
</template>
<script>
import { mapState } from "vuex";

export default {
    name: "RunJobAction",
    props: {
        actionId: {
            type: String,
            mandatory: true
        }
    },
    computed: {
        ...mapState(["current_tracker"]),
        ...mapState("transitionModal", [
            "current_transition",
            "post_actions_by_unique_id",
            "is_modal_save_running"
        ]),
        job_url_input_id() {
            return `post-action-${this.actionId}-job-url`;
        },
        post_action() {
            return this.post_actions_by_unique_id[this.actionId];
        },
        job_url: {
            get() {
                return this.post_action.job_url;
            },
            set(job_url) {
                this.$store.commit("transitionModal/updatePostAction", {
                    ...this.post_action,
                    job_url
                });
            }
        }
    }
};
</script>
