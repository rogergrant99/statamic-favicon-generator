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
                    Current value: "{{ formData.api_key }}" (length: {{ (formData.api_key || '').length }})
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
                    placeholder="Path to master icon (e.g., images/favicon-master.png)"
                />
                <p class="text-xs text-gray-600 mt-1">
                    Current value: "{{ formData.icon }}" | 
                    <a href="/cp/assets" target="_blank" class="text-blue-600 hover:underline">Browse assets</a>
                </p>
            </div>

            <!-- Generated HTML Tags (Read-only) -->
            <div>
                <label class="font-semibold block mb-2">Generated HTML Tags</label>
                <div class="bg-gray-900 text-gray-100 p-4 rounded font-mono text-xs overflow-x-auto max-h-64">
                    <pre v-if="formData.html_tags">{{ formData.html_tags }}</pre>
                    <div v-else class="text-gray-500">No HTML tags generated yet. Click "{{ generate }}" to generate favicons.</div>
                </div>
            </div>
        </div>

        <!-- Debug -->
        <details class="mt-6">
            <summary class="cursor-pointer text-sm text-gray-600 font-semibold">Debug Info</summary>
            <div class="mt-2 space-y-2">
                <div class="bg-green-50 border border-green-200 p-3 rounded">
                    <strong>Form Data:</strong>
                    <pre class="text-xs mt-1">{{ JSON.stringify(formData, null, 2) }}</pre>
                </div>
            </div>
        </details>
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
            saving: false
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
        
        console.log('Initialized form data:', this.formData);
    },
    
    methods: {
        getIconPath(icon) {
            if (Array.isArray(icon)) {
                return icon.length > 0 ? icon[0] : '';
            }
            return icon || '';
        },
        
        save() {
            console.log('Saving with data:', this.formData);
            
            if (!this.formData.api_key || this.formData.api_key.trim() === '') {
                this.$toast.error('Please enter an API key');
                return;
            }
            
            if (!this.formData.icon || this.formData.icon.trim() === '') {
                this.$toast.error('Please select a master icon');
                return;
            }
            
            this.saving = true;
            this.$progress.start();
            
            const payload = {
                api_key: this.formData.api_key,
                icon: this.formData.icon,
                html_tags: this.formData.html_tags,
                generated_at: this.formData.generated_at
            };
            
            console.log('Sending payload:', payload);
            
            this.$axios.post('/cp/favicon-generator/update', payload)
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
                    console.error('Error:', error);
                    
                    const errorMsg = error.response?.data?.message || error.message || 'Failed to generate favicons';
                    this.$toast.error(errorMsg);
                });
        }
    }
}
</script>