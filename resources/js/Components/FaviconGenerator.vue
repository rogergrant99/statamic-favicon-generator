<template>
    <div class="max-w-3xl mx-auto p-6">
        <header class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl">{{ title }}</h1>
                    <p v-if="formData.generated_at" class="text-sm text-gray-600 mt-1">
                        Last generated: {{ formData.generated_at }}
                    </p>
                </div>
                <button 
                    class="btn-primary"
                    @click="save"
                    :disabled="saving"
                >
                    <span v-if="saving">Generating...</span>
                    <span v-else>{{ generate }}</span>
                </button>
            </div>
        </header>

        <div class="card p-6 space-y-6">
            <!-- API Key -->
            <div>
                <label class="font-semibold block mb-2">API Key</label>
                <input
                    :value="formData.api_key"
                    @input="formData.api_key = $event.target.value"
                    type="text"
                    class="input-text"
                    placeholder="Enter your RealFaviconGenerator API key"
                />
                <p class="text-xs text-gray-600 mt-1">
                    Get your API key from <a href="https://realfavicongenerator.net/api" target="_blank" class="text-blue-600 hover:underline">RealFaviconGenerator.net</a>
                </p>
            </div>

            <!-- Icon URL -->
            <div>
                <label class="font-semibold block mb-2">Master Icon URL</label>
                <input
                    :value="formData.icon_url"
                    @input="formData.icon_url = $event.target.value"
                    type="url"
                    class="input-text"
                    placeholder="https://yoursite.com/assets/favicons/icon.png"
                />
                <p class="text-xs text-gray-600 mt-1">
                    Enter the full public URL to your master favicon image (must be at least 512x512px and publicly accessible).
                    <br>
                    <a href="/cp/assets" target="_blank" class="text-blue-600 hover:underline">Browse assets</a>, right-click on your image, and copy the URL.
                </p>
            </div>

            <!-- Generated HTML Tags (Read-only) -->
            <div>
                <label class="font-semibold block mb-2">Generated HTML Tags</label>
                <div class="bg-gray-900 text-gray-100 p-4 rounded font-mono text-xs overflow-x-auto max-h-64">
                    <pre v-if="formData.html_tags">{{ formData.html_tags }}</pre>
                    <div v-else class="text-gray-500">No HTML tags generated yet.</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { reactive } from 'vue';

export default {
    props: {
        title: String,
        blueprint: Object,
        meta: Object,
        initialValues: Object,
        generate: String
    },
    
    data() {
        return {
            formData: reactive({
                api_key: '',
                icon_url: '',
                html_tags: '',
                generated_at: ''
            }),
            saving: false
        }
    },
    
    mounted() {
        if (this.initialValues) {
            this.formData.api_key = this.initialValues.api_key || '';
            this.formData.icon_url = this.initialValues.icon_url || '';
            this.formData.html_tags = this.initialValues.html_tags || '';
            this.formData.generated_at = this.initialValues.generated_at || '';
        }
    },
    
    methods: {
        save() {
            if (!this.formData.api_key?.trim()) {
                this.$toast.error('Please enter an API key');
                return;
            }
            
            if (!this.formData.icon_url?.trim()) {
                this.$toast.error('Please enter the icon URL');
                return;
            }
            
            this.saving = true;
            this.$progress.start();
            
            this.$axios.post('/cp/favicon-generator/update', {
                api_key: this.formData.api_key,
                icon_url: this.formData.icon_url,
                html_tags: this.formData.html_tags,
                generated_at: this.formData.generated_at
            })
                .then((response) => {
                    this.saving = false;
                    this.$progress.complete();
                    
                    if (response.data.status === 'success') {
                        this.$toast.success(response.data.msg || 'Favicons generated successfully');
                        
                        if (response.data.generated_at) {
                            this.formData.generated_at = response.data.generated_at;
                        }
                        
                        if (response.data.html_tags) {
                            this.formData.html_tags = response.data.html_tags;
                        }
                    } else {
                        this.$toast.error(response.data.msg || 'An error occurred');
                    }
                })
                .catch((error) => {
                    this.saving = false;
                    this.$progress.complete();
                    this.$toast.error(error.response?.data?.message || 'Failed to generate favicons');
                });
        }
    }
}
</script>