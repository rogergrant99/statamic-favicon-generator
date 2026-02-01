<template>
    <div class="max-w-3xl mx-auto p-6">
        <h1>Favicon Generator - Debug</h1>
        <p>Component is rendering</p>
        
        <div class="mt-4">
            <pre class="bg-gray-100 p-4 text-xs overflow-auto">{{ debugInfo }}</pre>
        </div>
        
        <button class="btn-primary mt-4" @click="testClick">Test Button</button>
    </div>
</template>

<script>
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
            values: this.initialValues || {},
            saving: false
        }
    },
    
    computed: {
        debugInfo() {
            return {
                hasBlueprint: !!this.blueprint,
                hasValues: !!this.values,
                valueKeys: Object.keys(this.values || {}),
                title: this.title
            }
        }
    },
    
    methods: {
        testClick() {
            alert('Button works!');
            console.log('Values:', this.values);
            console.log('Blueprint:', this.blueprint);
        }
    }
}
</script>