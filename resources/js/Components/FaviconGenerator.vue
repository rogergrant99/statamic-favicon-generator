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

            <!-- Icon / Master Image -->
            <div>
                <label class="font-semibold block mb-2">Master Icon</label>
                <input
                    :value="formData.icon"
                    @input="formData.icon = $event.target.value"
                    type="text"
                    class="input-text"
                    placeholder="assets::path/to/file.png or container::path/to/file.png"
                />
                <p class="text-xs text-gray-600 mt-1">
                    Format: <code class="bg-gray-100 px-1">container::path/to/file.png</code> (e.g., <code class="bg-gray-100 px-1">assets::favicons/icon.png</code>)
                    <br>
                    <a href="/cp/assets" target="_blank" class="text-blue-600 hover:underline">Browse assets</a> to find the file
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

        <!-- Validation Errors -->
        <div v-if="validationErrors" class="mt-4 p-4 bg-red-50 border border-red-200 rounded">
            <h3 class="font-semibold text-red-800 mb-2">Validation Errors:</h3>
            <ul class="list-disc list-inside text-sm text-red-700">
                <li v-for="(errors, field) in validationErrors" :key="field">
                    <strong>{{ field }}:</strong> {{ errors.join(', ') }}
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import { reactive } from 'vue';

export default {
    props: {
        title: {
            type: String,
            default: 'Favicon Generator'
        },
        blueprint: Object,
        meta: Object,
        initialValues: Object,
        generate: {
            type: String,
            default: 'Generate Favicons'
        }
    },
    
    data() {
        return {
            formData: reactive({
                api_key: '',
                icon: '',
                html_tags: '',
                generated_at: ''
            }),
            saving: false,
            validationErrors: null
        }
    },
    
    mounted() {
        // Initialize form data from initial values
        if (this.initialValues) {
            this.formData.api_key = this.initialValues.api_key || '';
            this.formData.icon = this.getIconPath(this.initialValues.icon);
            this.formData.html_tags = this.initialValues.html_tags || '';
            this.formData.generated_at = this.initialValues.generated_at || '';
        }
        
        console.log('Initial values received:', this.initialValues);
        console.log('Meta data:', this.meta);
    },
    
    methods: {
        getIconPath(icon) {
            if (Array.isArray(icon)) {
                return icon.length > 0 ? icon[0] : '';
            }
            return icon || '';
        },
        
        save() {
            this.validationErrors = null;
            
            if (!this.formData.api_key || this.formData.api_key.trim() === '') {
                this.$toast.error('Please enter an API key');
                return;
            }
            
            if (!this.formData.icon || this.formData.icon.trim() === '') {
                this.$toast.error('Please select a master icon');
                return;
            }
            
            // Validate icon format
            const iconValue = this.formData.icon.trim();
            if (!iconValue.includes('::')) {
                this.$toast.error('Icon must be in format: container::path/to/file.png (e.g., assets::favicons/icon.png)');
                return;
            }
            
            this.saving = true;
            this.$progress.start();
            
            // Format payload to match what the blueprint expects
            const payload = {
                api_key: this.formData.api_key,
                icon: [iconValue], // Send as array with full container::path format
                html_tags: this.formData.html_tags,
                generated_at: this.formData.generated_at,
                settings_introduction: this.initialValues.settings_introduction || ''
            };
            
            console.log('Sending payload:', payload);
            
            this.$axios.post('/cp/favicon-generator/update', payload)
                .then((response) => {
                    this.saving = false;
                    this.$progress.complete();
                    
                    console.log('Success response:', response.data);
                    
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
                    
                    console.error('Full error:', error);
                    console.error('Error response data:', error.response?.data);
                    
                    // Show validation errors if they exist
                    if (error.response?.status === 422 && error.response?.data?.errors) {
                        this.validationErrors = error.response.data.errors;
                        this.$toast.error('Validation failed. Check the errors below.');
                    } else if (error.response?.data?.message) {
                        this.$toast.error(error.response.data.message);
                    } else {
                        const errorMsg = error.message || 'Failed to generate favicons';
                        this.$toast.error(errorMsg);
                    }
                });
        }
    }
}
</script>