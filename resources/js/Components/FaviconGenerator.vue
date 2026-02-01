<template>
    <div class="max-w-3xl mx-auto p-6">
        <header class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl">{{ title }}</h1>
                    <p v-if="values.generated_at" class="text-sm text-gray-600 mt-1">
                        Last generated: {{ values.generated_at }}
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
            <!-- Settings Introduction -->
            <div v-if="values.settings_introduction">
                <div class="prose prose-sm" v-html="values.settings_introduction"></div>
            </div>

            <!-- API Key -->
            <div>
                <label class="font-semibold block mb-2">API Key</label>
                <input
                    v-model="values.api_key"
                    type="text"
                    class="input-text"
                    placeholder="Enter your RealFaviconGenerator API key"
                />
            </div>

            <!-- Icon / Master Image -->
            <div>
                <label class="font-semibold block mb-2">Master Icon</label>
                <div v-if="values.icon" class="mb-2 p-3 bg-gray-50 border rounded">
                    <div class="text-sm">
                        <strong>Current:</strong> {{ values.icon }}
                    </div>
                </div>
                <input
                    v-model="values.icon"
                    type="text"
                    class="input-text"
                    placeholder="Path to master icon (e.g., images/favicon-master.png)"
                />
                <p class="text-xs text-gray-600 mt-1">
                    Enter the asset path or 
                    <a href="/cp/assets" target="_blank" class="text-blue-600 hover:underline">browse assets</a>
                    to find your master favicon image
                </p>
            </div>

            <!-- Generated HTML Tags (Read-only) -->
            <div>
                <label class="font-semibold block mb-2">Generated HTML Tags</label>
                <div class="bg-gray-900 text-gray-100 p-4 rounded font-mono text-xs overflow-x-auto">
                    <pre v-if="values.html_tags">{{ values.html_tags }}</pre>
                    <div v-else class="text-gray-500">No HTML tags generated yet. Click "{{ generate }}" to generate favicons.</div>
                </div>
            </div>
        </div>

        <!-- Debug (remove this later) -->
        <details class="mt-6">
            <summary class="cursor-pointer text-sm text-gray-600">Debug Info</summary>
            <pre class="bg-gray-100 p-4 text-xs overflow-auto mt-2">{{ values }}</pre>
        </details>
    </div>
</template>

<script>
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
            values: { ...this.initialValues } || {},
            saving: false
        }
    },
    
    methods: {
        save() {
            if (!this.values.api_key) {
                this.$toast.error('Please enter an API key');
                return;
            }
            
            if (!this.values.icon) {
                this.$toast.error('Please select a master icon');
                return;
            }
            
            this.saving = true;
            this.$progress.start();
            
            this.$axios.post('/cp/favicon-generator/update', this.values)
                .then((response) => {
                    this.saving = false;
                    this.$progress.complete();
                    
                    if (response.data.status === 'success') {
                        this.$toast.success(response.data.msg || 'Favicons generated successfully');
                        
                        // Update values from response
                        if (response.data.generated_at) {
                            this.values.generated_at = response.data.generated_at;
                        }
                        
                        if (response.data.html_tags) {
                            this.values.html_tags = response.data.html_tags;
                        }
                    } else {
                        this.$toast.error(response.data.msg || 'An error occurred');
                    }
                })
                .catch((error) => {
                    this.saving = false;
                    this.$progress.complete();
                    console.error('Favicon generation error:', error);
                    
                    const errorMsg = error.response?.data?.message || error.message || 'Failed to generate favicons';
                    this.$toast.error(errorMsg);
                });
        }
    }
}
</script>