<template>
    <div class="max-w-xl">
        <header class="mb-6">
            <h1 class="mb-2">{{ title }}</h1>
            <p v-if="values && values.generated_at" class="text-xs text-gray-600">
                Last generated: {{ values.generated_at }}
            </p>
        </header>

        <div class="card p-0">
            <div v-for="(field, handle) in fields" :key="handle" class="p-4 border-b last:border-b-0">
                <div class="mb-2">
                    <label class="font-semibold">{{ field.display }}</label>
                    <p v-if="field.instructions" class="text-xs text-gray-600 mt-1">
                        {{ field.instructions }}
                    </p>
                </div>
                
                <input
                    v-if="field.type === 'text'"
                    v-model="values[handle]"
                    type="text"
                    class="input-text"
                />
                
                <textarea
                    v-else-if="field.type === 'textarea'"
                    v-model="values[handle]"
                    class="input-text"
                    rows="3"
                />
                
                <input
                    v-else-if="field.type === 'integer'"
                    v-model.number="values[handle]"
                    type="number"
                    class="input-text"
                />
                
                <toggle-input
                    v-else-if="field.type === 'toggle'"
                    v-model="values[handle]"
                />
                
                <div v-else class="text-gray-500 text-sm">
                    Field type "{{ field.type }}" not supported in simple mode
                </div>
            </div>
        </div>

        <div class="mt-4 flex justify-end">
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