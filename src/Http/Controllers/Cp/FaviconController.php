<template>
    <div class="max-w-3xl">
        <header class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1>{{ title }}</h1>
                    <p v-if="values && values.generated_at" class="text-xs text-gray-600 mt-1">
                        Last generated: {{ values.generated_at }}
                    </p>
                </div>
                <button 
                    class="btn-primary"
                    @click="save"
                    :disabled="saving"
                >
                    <span v-if="saving">
                        <svg class="inline w-4 h-4 animate-spin mr-1" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                        </svg>
                        Generating...
                    </span>
                    <span v-else>{{ generate }}</span>
                </button>
            </div>
        </header>

        <div class="card p-0">
            <div v-for="(field, handle) in fields" :key="handle" class="p-4 border-b last:border-b-0">
                <div class="mb-2">
                    <label class="font-semibold">{{ field.display }}</label>
                    <p v-if="field.instructions" class="text-xs text-gray-600 mt-1">
                        {{ field.instructions }}
                    </p>
                </div>
                
                <!-- Text fields -->
                <input
                    v-if="field.type === 'text'"
                    v-model="values[handle]"
                    type="text"
                    class="input-text"
                />
                
                <!-- Textarea -->
                <textarea
                    v-else-if="field.type === 'textarea'"
                    v-model="values[handle]"
                    class="input-text"
                    rows="3"
                />
                
                <!-- Integer/Number -->
                <input
                    v-else-if="field.type === 'integer'"
                    v-model.number="values[handle]"
                    type="number"
                    class="input-text"
                />
                
                <!-- Toggle -->
                <toggle-input
                    v-else-if="field.type === 'toggle'"
                    v-model="values[handle]"
                />
                
                <!-- Assets fieldtype - Manual render -->
                <div v-else-if="field.type === 'assets'">
                    <div class="asset-selector">
                        <div v-if="values[handle]" class="flex items-center gap-3 p-3 border rounded bg-gray-50">
                            <div class="text-sm flex-1">
                                <div class="font-medium">{{ values[handle] }}</div>
                                <div class="text-xs text-gray-600">Current asset path</div>
                            </div>
                            <button 
                                @click="openAssetManager(handle, field)"
                                type="button"
                                class="btn-sm"
                            >
                                Change
                            </button>
                        </div>
                        <button 
                            v-else
                            @click="openAssetManager(handle, field)"
                            type="button"
                            class="btn"
                        >
                            Choose Asset
                        </button>
                    </div>
                </div>
                
                <!-- HTML Code (read-only) -->
                <div v-else-if="field.type === 'code' || handle === 'html_tags'">
                    <div class="bg-gray-900 text-gray-100 p-4 rounded font-mono text-sm overflow-x-auto">
                        <pre>{{ values[handle] || 'No HTML tags generated yet' }}</pre>
                    </div>
                </div>
                
                <!-- Unsupported field type -->
                <div v-else class="text-gray-500 text-sm">
                    Field type "{{ field.type }}" - value: {{ values[handle] }}
                </div>
            </div>
        </div>
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
            default: 'Generate'
        }
    },
    
    data() {
        return {
            values: { ...this.initialValues } || {},
            saving: false
        }
    },
    
    computed: {
        fields() {
            if (!this.blueprint || !this.blueprint.tabs) return {};
            
            const allFields = {};
            this.blueprint.tabs.forEach(tab => {
                if (tab.sections) {
                    tab.sections.forEach(section => {
                        if (section.fields) {
                            section.fields.forEach(field => {
                                allFields[field.handle] = field;
                            });
                        }
                    });
                }
            });
            return allFields;
        }
    },
    
    methods: {
        openAssetManager(handle, field) {
            // Open Statamic's asset browser
            const container = field.container || 'assets';
            const maxFiles = field.max_files || 1;
            
            // Navigate to asset browser - user can copy the path
            window.open(`/cp/assets/browse/${container}`, '_blank');
            
            // Show a prompt for manual entry as fallback
            this.$nextTick(() => {
                const newPath = prompt('Enter the asset path (or browse assets in the new tab):', this.values[handle] || '');
                if (newPath !== null) {
                    this.values[handle] = newPath;
                }
            });
        },
        
        save() {
            this.saving = true;
            this.$progress.start();
            
            this.$axios.post('/cp/favicon-generator/update', this.values)
                .then((response) => {
                    this.saving = false;
                    this.$progress.complete();
                    
                    if (response.data.status === 'success') {
                        this.$toast.success(response.data.msg || 'Favicons generated successfully');
                        
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
                    this.$toast.error('Failed to generate favicons');
                });
        }
    }
}
</script>