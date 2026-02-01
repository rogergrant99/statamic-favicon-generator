<template>
    <publish-container
        name="favicon-settings"
        :blueprint="blueprint"
        :meta="meta"
        :values="values"
        @updated="values = $event"
    >
        <template #default="{ container, setFieldValue }">
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

            <publish-fields
                :fields="allFields"
                @updated="setFieldValue($event.handle, $event.value)"
                @meta-updated="container.setFieldMeta($event.handle, $event.value)"
            />
        </template>
    </publish-container>
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
        allFields() {
            if (!this.blueprint || !this.blueprint.tabs) return [];
            
            const fields = [];
            this.blueprint.tabs.forEach(tab => {
                if (tab.sections) {
                    tab.sections.forEach(section => {
                        if (section.fields) {
                            fields.push(...section.fields);
                        }
                    });
                }
            });
            return fields;
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