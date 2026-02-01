<template>
    <div class="max-w-3xl mx-auto p-6">
        <header class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl">{{ title }}</h1>
                                <p class="text-sm text-gray-600 mt-1">
                                    Last generated: {{ formData.generated_at }}
                                </p>                </div>
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
                    v-model="formData.api_key"
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
                    v-model="formData.icon"
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
                settings_introduction: true,
                api_key: '',
                icon: '',
                html_tags: '',
                generated_at: ''
            }),
            saving: false
        }
    },
    
    mounted() {
        if (this.initialValues) {
            // Assign properties directly from initialValues
            // Use Object.assign to merge properties into the reactive formData object
            Object.assign(this.formData, {
                settings_introduction: this.initialValues.settings_introduction !== undefined ? this.initialValues.settings_introduction : true,
                api_key: this.initialValues.api_key !== undefined ? this.initialValues.api_key : '',
                icon: this.initialValues.icon !== undefined ? this.initialValues.icon : '',
                html_tags: this.initialValues.html_tags !== undefined ? this.initialValues.html_tags : '',
                generated_at: this.initialValues.generated_at !== undefined ? this.initialValues.generated_at : ''
            });
        }
          },
          
          methods: {        save() {
            if (!this.formData.api_key?.trim()) {
                this.$toast.error('Please enter an API key');
                return;
            }
            
            if (!this.formData.icon?.trim()) {
                this.$toast.error('Please enter the icon URL');
                return;
            }
            
            this.saving = true;
            this.$progress.start();
            
            const payload = {
                settings_introduction: this.formData.settings_introduction,
                api_key: this.formData.api_key,
                icon: this.formData.icon, // Send as string (URL)
                html_tags: this.formData.html_tags,
                generated_at: this.formData.generated_at
            };
            
            console.log('Sending:', payload);
            
            this.$axios.post('/cp/favicon-generator/update', payload)
                .then((response) => {
                    this.saving = false;
                    this.$progress.complete();
                    
                    console.log('Response:', response.data);
                    
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
                    
                    console.error('Error:', error.response?.data);
                    this.$toast.error(error.response?.data?.message || 'Failed to generate favicons');
                });
        }
    }
}
</script>