<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-slate-100 p-6">
        <div class="max-w-3xl mx-auto">
            <!-- Header Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-4 border border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 mb-1">{{ title }}</h1>
                        <p class="text-xs text-slate-500 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span v-if="formattedGeneratedAt">Last generated: {{ formattedGeneratedAt }}</span>
                            <span v-else class="text-slate-400">Not generated yet</span>
                        </p>
                    </div>
                    <button 
                        class="bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold px-5 py-2.5 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 text-sm"
                        @click="save"
                        :disabled="saving"
                    >
                        <svg v-if="saving" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span v-if="saving">Generating...</span>
                        <span v-else>{{ generate }}</span>
                    </button>
                </div>
            </div>

            <!-- Configuration Card -->
            <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-3">
                    <h2 class="text-white font-semibold text-base flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Configuration
                    </h2>
                </div>

                <div class="p-6 space-y-6">
                    <!-- API Key Section -->
                    <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                        <label class="font-semibold text-slate-900 block mb-2 flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            API Key
                        </label>
                        <input
                            :value="formData.api_key"
                            @input="formData.api_key = $event.target.value"
                            type="text"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all font-mono text-sm bg-white"
                            placeholder="Enter your RealFaviconGenerator API key"
                        />
                        <p class="text-xs text-slate-600 mt-2 flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Get your API key from <a href="https://realfavicongenerator.net/api" target="_blank" class="text-blue-600 hover:text-blue-700 underline font-medium">RealFaviconGenerator.net</a></span>
                        </p>
                    </div>

                    <!-- Icon Section -->
                    <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                        <label class="font-semibold text-slate-900 block mb-2 flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Master Icon
                        </label>
                        
                        <div class="space-y-3">
                            <!-- Preview Area -->
                            <div v-if="iconPreview" class="flex items-center gap-3 bg-white border border-slate-300 rounded-lg p-3">
                                <img :src="iconPreview" class="w-16 h-16 object-cover rounded border border-slate-200" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-900 truncate">{{ iconFileName || 'Selected Image' }}</p>
                                    <p class="text-xs text-slate-500">Ready to generate</p>
                                </div>
                                <button 
                                    @click="clearIcon"
                                    class="text-slate-400 hover:text-red-600 transition-colors"
                                    type="button"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            
                            <!-- Upload Button -->
                            <div>
                                <input 
                                    type="file" 
                                    accept="image/*"
                                    @change="handleFileSelect"
                                    id="fileInput"
                                    class="hidden"
                                />
                                <button 
                                    @click="triggerFileInput"
                                    type="button"
                                    class="bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold px-4 py-2 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center gap-2 text-sm"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <span>{{ iconPreview ? 'Change Image' : 'Browse...' }}</span>
                                </button>
                            </div>
                        </div>
                        
                        <p class="text-xs text-slate-600 mt-2 flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>
                                Upload your master favicon image (must be at least 512x512px, PNG recommended).
                            </span>
                        </p>
                    </div>

                    <!-- Generated HTML Tags Section -->
                    <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                        <label class="font-semibold text-slate-900 block mb-2 flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                            </svg>
                            Generated HTML Tags
                        </label>
                        <div class="bg-slate-900 rounded-lg p-4 overflow-x-auto max-h-64 border border-slate-700 shadow-inner">
                            <pre v-if="formData.html_tags" class="text-gray-100 font-mono text-xs leading-relaxed" style="color: #f3f4f6;"><code style="color: #f3f4f6;">{{ formData.html_tags }}</code></pre>
                            <div v-else class="text-slate-400 text-xs font-mono flex items-center gap-2 justify-center py-6">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                No HTML tags generated yet
                            </div>
                        </div>
                        <p class="text-xs text-slate-600 mt-2 flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Copy these tags and paste them into the <code class="bg-slate-200 px-1.5 py-0.5 rounded text-xs font-mono">&lt;head&gt;</code> section of your HTML</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer Info -->
            <div class="mt-4 text-center text-xs text-slate-500">
                <p class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Powered by RealFaviconGenerator
                </p>
            </div>
        </div>
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
        formData: {
            settings_introduction: true,
            api_key: '',
            icon: '',
            html_tags: '',
            generated_at: ''
        },
        saving: false,
        iconPreview: null,
        iconFileName: null
    }
},

computed: {
    formattedGeneratedAt() {
        if (!this.formData.generated_at) {
            return '';
        }
        
        try {
            const timestamp = this.formData.generated_at;
            
            // Handle 'YYYY-MM-DD HH:MM:SS' format (treat as UTC)
            if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(timestamp)) {
                // Replace space with 'T' and append 'Z' to make it ISO format UTC
                const isoString = timestamp.replace(' ', 'T') + 'Z';
                const utcDate = new Date(isoString);
                
                if (isNaN(utcDate.getTime())) {
                    console.log('Invalid date after conversion:', timestamp);
                    return timestamp;
                }
                
                // Format to local timezone
                const options = {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                };
                
                return utcDate.toLocaleString('en-US', options);
            }
            
            // Fall back to original logic for other formats
            const utcDate = new Date(timestamp);
            
            if (isNaN(utcDate.getTime())) {
                console.log('Invalid date, returning original:', timestamp);
                return timestamp;
            }
            
            return utcDate.toLocaleString();
        } catch (error) {
            console.error('Error formatting date:', error);
            return this.formData.generated_at;
        }
    }
},
    
mounted() {
    if (this.initialValues) {
        if (this.initialValues.settings_introduction !== undefined) {
            this.formData.settings_introduction = this.initialValues.settings_introduction;
        }
        if (this.initialValues.api_key !== undefined) {
            this.formData.api_key = this.initialValues.api_key;
        }
        if (this.initialValues.icon !== undefined) {
            this.formData.icon = this.initialValues.icon;
            // If there's an existing icon (URL or path), show it as preview
            if (this.initialValues.icon) {
                this.iconPreview = this.initialValues.icon;
            }
        }
        if (this.initialValues.html_tags !== undefined) {
            this.formData.html_tags = this.initialValues.html_tags;
        }
        if (this.initialValues.generated_at !== undefined) {
            this.formData.generated_at = this.initialValues.generated_at;
        }
    }
},
          
methods: {
    triggerFileInput() {
        document.getElementById('fileInput').click();
    },
    
    handleFileSelect(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        // Validate file type
        if (!file.type.startsWith('image/')) {
            this.$toast.error('Please select an image file');
            return;
        }
        
        // Store file name
        this.iconFileName = file.name;
        
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            this.iconPreview = e.target.result;
            // Store the base64 data directly
            this.formData.icon = e.target.result;
        };
        reader.readAsDataURL(file);
    },
    
    clearIcon() {
        this.formData.icon = '';
        this.iconPreview = null;
        this.iconFileName = null;
    },
    
    save() {
        if (!this.formData.api_key?.trim()) {
            this.$toast.error('Please enter an API key');
            return;
        }
        
        if (!this.formData.icon) {
            this.$toast.error('Please select an icon');
            return;
        }
        
        this.saving = true;
        this.$progress.start();
        
        const payload = {
            settings_introduction: this.formData.settings_introduction,
            api_key: this.formData.api_key,
            icon: this.formData.icon,
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
                    
                    // Update the generated_at timestamp
                    if (response.data.generated_at) {
                        this.formData.generated_at = response.data.generated_at;
                    } else {
                        // If server doesn't return generated_at, create a UTC timestamp
                        // in the same format as YAML: 'YYYY-MM-DD HH:MM:SS'
                        const now = new Date();
                        const year = now.getUTCFullYear();
                        const month = String(now.getUTCMonth() + 1).padStart(2, '0');
                        const day = String(now.getUTCDate()).padStart(2, '0');
                        const hours = String(now.getUTCHours()).padStart(2, '0');
                        const minutes = String(now.getUTCMinutes()).padStart(2, '0');
                        const seconds = String(now.getUTCSeconds()).padStart(2, '0');
                        
                        this.formData.generated_at = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
                    }
                    
                    // Update the HTML tags
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