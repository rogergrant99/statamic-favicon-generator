<template>
    <publish-container
        ref="container"
        name="base"
        :blueprint="blueprint"
        :meta="meta"
        v-model="values"
    >
        <div>
            <div class="flex items-center justify-between mb-3">
                <div class="float-left">
                    <h1>Favicon Generator</h1>
                    <small v-if="values && values.generated_at" class="block text-xs text-gray-80">
                        Last generated: {{ values.generated_at }}
                    </small>
                </div>
                <button class="btn-primary" @click="save">{{ generate }}</button>
            </div>
            <publish-tabs
                @updated="updateValue"
                @meta-updated="updateMeta" 
            />
        </div>
    </publish-container>
</template>

<script>
export default {
    props: {
        blueprint: Object,
        meta: Object,
        initialValues: Object,
        generate: String
    },
    
    data() {
        return {
            values: this.initialValues || {}
        }
    },
    
    methods: {
        updateValue(handle, value) {
            const container = this.$refs.container;
            if (container && container.setFieldValue) {
                container.setFieldValue(handle, value);
            }
        },
        
        updateMeta(handle, value) {
            const container = this.$refs.container;
            if (container && container.setFieldMeta) {
                container.setFieldMeta(handle, value);
            }
        },
        
        save() {
            this.$progress.start();
            
            Statamic.$axios.post('/cp/favicon-generator/update', this.values)
                .then((response) => {
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
                    this.$progress.complete();
                    console.error('Favicon generation error:', error);
                    this.$toast.error('Failed to generate favicons');
                });
        }
    }
}
</script>