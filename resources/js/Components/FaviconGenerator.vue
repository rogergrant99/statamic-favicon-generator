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
                
                <!-- Assets fieldtype -->
                <div v-else-if="field.type === 'assets'">
                    <div 
                        class="asset-fieldtype"
                        @click="openAssetBrowser(handle, field)"
                    >
                        <div v-if="values[handle]" class="flex items-center gap-2 p-2 border rounded">
                            <img 
                                v-if="getAssetUrl(values[handle])" 
                                :src="getAssetUrl(values[handle])" 
                                class="w-12 h-12 object-cover rounded"
                            />
                            <span class="text-sm">{{ getAssetFilename(values[handle]) }}</span>
                            <button 
                                @click.stop="clearAsset(handle)"
                                class="ml-auto text-red-500 hover:text-red-700"
                            >
                                ✕
                            </button>
                        </div>
                        <button 
                            v-else
                            type="button"
                            class="btn"
                        >
                            Choose File
                        </button>
                    </div>
                </div>
                
                <!-- Unsupported field type -->
                <div v-else class="text-gray-500 text-sm">
                    Field type "{{ field.type }}" not yet supported
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
        openAssetBrowser(handle, field) {
            // Use Statamic's asset browser
            this.$axios.get(`/cp/assets/browse`, {
                params: {
                    container: field.container || 'assets',
                    folder: field.folder || '/',
                    max_files: field.max_files || 1
                }
            }).then(response => {
                // This won't work directly - we need to use Statamic's built-in selector
                // Let's try opening it via event
                this.$events.$emit('asset-browser.open', {
                    container: field.container || 'assets',
                    folder: field.folder || '/',
                    maxFiles: field.max_files || 1,
                    restrictFolders: field.restrict || false,
                    onSelect: (selections) => {
                        if (selections && selections.length > 0) {
                            this.values[handle] = selections[0].id || selections[0].path;
                        }
                    }
                });
            });
        },
        
        clearAsset(handle) {
            this.values[handle] = null;
        },
        
        getAssetUrl(assetPath) {
            if (!assetPath) return null;
            // Construct asset URL - adjust based on your setup
            return `/assets/${assetPath}`;
        },
        
        getAssetFilename(assetPath) {
            if (!assetPath) return '';
            return assetPath.split('/').pop();
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