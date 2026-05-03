@if (Webkul\Product\Helpers\ProductType::hasVariants($product->type))
    {!! view_render_event('bagisto.shop.products.view.configurable-options.before', ['product' => $product]) !!}

    <v-product-configurable-options :errors="errors"></v-product-configurable-options>

    {!! view_render_event('bagisto.shop.products.view.configurable-options.after', ['product' => $product]) !!}

    @push('scripts')
        <script
            type="text/x-template"
            id="v-product-configurable-options-template"
        >
            <div class="max-w-full max-sm:w-full">
                <input
                    type="hidden"
                    name="selected_configurable_option"
                    id="selected_configurable_option"
                    :value="selectedOptionVariant"
                    ref="selected_configurable_option"
                >

                <div
                    class="mt-6"
                    v-for='(attribute, index) in childAttributes'
                >
                    <!-- Dropdown Options Container -->
                    <template v-if="! attribute.swatch_type || attribute.swatch_type == '' || attribute.swatch_type == 'dropdown'">
                        <h2 class="mb-3 text-sm font-semibold text-slate-700 uppercase tracking-wide max-sm:text-xs">
                            @{{ attribute.label }}
                        </h2>

                        <v-field
                            as="select"
                            :name="'super_attribute[' + attribute.id + ']'"
                            class="block w-full cursor-pointer rounded-xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-600 focus:border-[#b95b27] focus:ring-1 focus:ring-[#b95b27] transition-colors"
                            :class="[errors['super_attribute[' + attribute.id + ']'] ? 'border border-red-500' : '']"
                            :id="'attribute_' + attribute.id"
                            v-model="attribute.selectedValue"
                            rules="required"
                            :label="attribute.label"
                            :aria-label="attribute.label"
                            :disabled="attribute.disabled"
                            @change="configure(attribute, $event.target.value)"
                        >
                            <option
                                v-for='(option, index) in attribute.options'
                                :value="option.id"
                            >
                                @{{ option.label }}
                            </option>
                        </v-field>
                    </template>

                    <!-- Swatch Options Container -->
                    <template v-else>
                        <h2 class="mb-3 text-sm font-semibold text-slate-700 uppercase tracking-wide max-sm:text-xs">
                            @{{ attribute.label }}
                        </h2>

                        <div class="flex flex-wrap items-center gap-3">
                            <template v-for="(option, index) in attribute.options">
                                <template v-if="option.id">
                                    <!-- Color Swatch Options -->
                                    <label
                                        class="relative flex cursor-pointer items-center justify-center rounded-full p-1 transition-all"
                                        :class="{'ring-2 ring-[#b95b27] ring-offset-2' : option.id == attribute.selectedValue}"
                                        :title="option.label"
                                        v-if="attribute.swatch_type == 'color'"
                                    >
                                        <v-field
                                            type="radio"
                                            :name="'super_attribute[' + attribute.id + ']'"
                                            :value="option.id"
                                            v-slot="{ field }"
                                            rules="required"
                                            :label="attribute.label"
                                            :aria-label="attribute.label"
                                        >
                                            <input
                                                type="radio"
                                                :name="'super_attribute[' + attribute.id + ']'"
                                                :value="option.id"
                                                v-bind="field"
                                                :id="'attribute_' + attribute.id"
                                                :aria-labelledby="'color-choice-' + index + '-label'"
                                                class="peer sr-only"
                                                @click="configure(attribute, $event.target.value)"
                                            />
                                        </v-field>

                                        <span
                                            class="h-16 w-16 rounded-full border border-slate-200 max-sm:h-12 max-sm:w-12"
                                            tabindex="0"
                                            :style="{ 'background-color': option.swatch_value }"
                                        ></span>
                                    </label>

                                    <!-- Image Swatch Options -->
                                    <label
                                        class="group relative flex h-16 w-16 cursor-pointer items-center justify-center overflow-hidden rounded-2xl border-2 bg-white transition-all max-sm:h-12 max-sm:w-12"
                                        :class="{'border-[#b95b27]' : option.id == attribute.selectedValue, 'border-slate-200 hover:border-slate-300' : option.id != attribute.selectedValue}"
                                        :title="option.label"
                                        v-if="attribute.swatch_type == 'image'"
                                    >
                                        <v-field
                                            type="radio"
                                            :name="'super_attribute[' + attribute.id + ']'"
                                            v-model="attribute.selectedValue"
                                            :value="option.id"
                                            v-slot="{ field }"
                                            rules="required"
                                            :label="attribute.label"
                                            :aria-label="attribute.label"
                                        >
                                            <input
                                                type="radio"
                                                :name="'super_attribute[' + attribute.id + ']'"
                                                :value="option.id"
                                                v-bind="field"
                                                :id="'attribute_' + attribute.id"
                                                :aria-labelledby="'color-choice-' + index + '-label'"
                                                class="peer sr-only"
                                                @click="configure(attribute, $event.target.value)"
                                            />
                                        </v-field>

                                        <img
                                            :src="option.swatch_value"
                                            :title="option.label"
                                            class="h-full w-full object-cover"
                                        />
                                    </label>

                                    <!-- Text Swatch Options (Size pills) -->
                                    <label
                                        class="group relative flex h-fit min-w-[48px] cursor-pointer items-center justify-center rounded-lg border bg-slate-100 px-4 py-2.5 font-medium text-slate-700 transition-all hover:bg-slate-200 max-sm:px-3 max-sm:py-2"
                                        :class="{'!bg-[#4d5223] !text-white border-[#4d5223]' : option.id == attribute.selectedValue, 'border-slate-200' : option.id != attribute.selectedValue}"
                                        :title="option.label"
                                        v-if="attribute.swatch_type == 'text'"
                                    >
                                        <v-field
                                            type="radio"
                                            :name="'super_attribute[' + attribute.id + ']'"
                                            :value="option.id"
                                            v-model="attribute.selectedValue"
                                            v-slot="{ field }"
                                            rules="required"
                                            :label="attribute.label"
                                            :aria-label="attribute.label"
                                        >
                                            <input
                                                type="radio"
                                                :name="'super_attribute[' + attribute.id + ']'"
                                                :value="option.id"
                                                v-bind="field"
                                                :id="'attribute_' + attribute.id"
                                                class="peer sr-only"
                                                :aria-labelledby="'color-choice-' + index + '-label'"
                                                @click="configure(attribute, $event.target.value)"
                                            />
                                        </v-field>

                                        <span class="text-sm max-sm:text-xs">
                                            @{{ option.label }}
                                        </span>
                                    </label>
                                </template>
                            </template>

                            <span
                                class="text-sm text-slate-400 max-sm:text-xs"
                                v-if="! attribute.options.length"
                            >
                                @lang('shop::app.products.view.type.configurable.select-above-options')
                            </span>
                        </div>
                    </template>

                    <v-error-message
                        :name="'super_attribute[' + attribute.id + ']'"
                        v-slot="{ message }"
                    >
                        <p class="mt-1 text-xs text-red-500">
                            @{{ message }}
                        </p>
                    </v-error-message>
                </div>
            </div>
        </script>

        <script type="module">
            let galleryImages = @json(product_image()->getGalleryImages($product));

            app.component('v-product-configurable-options', {
                template: '#v-product-configurable-options-template',

                props: ['errors'],

                data() {
                    return {
                        config: @json(app('Webkul\Product\Helpers\ConfigurableOption')->getConfigurationConfig($product)),

                        childAttributes: [],

                        possibleOptionVariant: null,

                        selectedOptionVariant: '',

                        galleryImages: [],
                    }
                },

                mounted() {
                    let attributes = JSON.parse(JSON.stringify(this.config)).attributes.slice();

                    let index = attributes.length;

                    while (index--) {
                        let attribute = attributes[index];

                        attribute.options = [];

                        if (index) {
                            attribute.disabled = true;
                        } else {
                            this.fillAttributeOptions(attribute);
                        }

                        attribute = Object.assign(attribute, {
                            childAttributes: this.childAttributes.slice(),
                            prevAttribute: attributes[index - 1],
                            nextAttribute: attributes[index + 1]
                        });

                        this.childAttributes.unshift(attribute);
                    }
                },

                methods: {
                    configure(attribute, optionId) {
                        this.possibleOptionVariant = this.getPossibleOptionVariant(attribute, optionId);

                        if (optionId) {
                            attribute.selectedValue = optionId;

                            if (attribute.nextAttribute) {
                                attribute.nextAttribute.disabled = false;

                                this.clearAttributeSelection(attribute.nextAttribute);

                                this.fillAttributeOptions(attribute.nextAttribute);

                                this.resetChildAttributes(attribute.nextAttribute);
                            } else {
                                this.selectedOptionVariant = this.possibleOptionVariant;
                            }
                        } else {
                            this.clearAttributeSelection(attribute);

                            this.clearAttributeSelection(attribute.nextAttribute);

                            this.resetChildAttributes(attribute);
                        }

                        this.reloadPrice();

                        this.reloadImages();
                    },

                    getPossibleOptionVariant(attribute, optionId) {
                        let matchedOptions = attribute.options.filter(option => option.id == optionId);

                        if (matchedOptions[0]?.allowedProducts) {
                            return matchedOptions[0].allowedProducts[0];
                        }

                        return undefined;
                    },

                    fillAttributeOptions(attribute) {
                        let options = this.config.attributes.find(tempAttribute => tempAttribute.id === attribute.id)?.options;

                        attribute.options = [{
                            'id': '',
                            'label': "@lang('shop::app.products.view.type.configurable.select-options')",
                            'products': []
                        }];

                        if (! options) {
                            return;
                        }

                        let prevAttributeSelectedOption = attribute.prevAttribute?.options.find(option => option.id == attribute.prevAttribute.selectedValue);

                        let index = 1;

                        for (let i = 0; i < options.length; i++) {
                            let allowedProducts = [];

                            if (prevAttributeSelectedOption) {
                                for (let j = 0; j < options[i].products.length; j++) {
                                    if (prevAttributeSelectedOption.allowedProducts && prevAttributeSelectedOption.allowedProducts.includes(options[i].products[j])) {
                                        allowedProducts.push(options[i].products[j]);
                                    }
                                }
                            } else {
                                allowedProducts = options[i].products.slice(0);
                            }

                            if (allowedProducts.length > 0) {
                                options[i].allowedProducts = allowedProducts;

                                attribute.options[index++] = options[i];
                            }
                        }
                    },

                    resetChildAttributes(attribute) {
                        if (! attribute.childAttributes) {
                            return;
                        }

                        attribute.childAttributes.forEach(function (set) {
                            set.selectedValue = null;

                            set.disabled = true;
                        });
                    },

                    clearAttributeSelection (attribute) {
                        if (! attribute) {
                            return;
                        }

                        attribute.selectedValue = null;

                        this.selectedOptionVariant = null;
                    },

                    reloadPrice () {
                        let selectedOptionCount = this.childAttributes.filter(attribute => attribute.selectedValue).length;

                        let finalPrice = document.querySelector('.final-price');

                        let regularPrice = document.querySelector('.regular-price');

                        let configVariant = this.config.variant_prices[this.possibleOptionVariant];

                        if (this.childAttributes.length == selectedOptionCount) {
                            document.querySelector('.price-label').style.display = 'none';

                            if (parseInt(configVariant.regular.price) > parseInt(configVariant.final.price)) {
                                regularPrice.style.display = 'block';

                                finalPrice.innerHTML = configVariant.final.formatted_price;

                                regularPrice.innerHTML = configVariant.regular.formatted_price;
                            } else {
                                finalPrice.innerHTML = configVariant.regular.formatted_price;

                                regularPrice.style.display = 'none';
                            }

                            this.$emitter.emit('configurable-variant-selected-event',this.possibleOptionVariant);
                        } else {
                            document.querySelector('.price-label').style.display = 'inline-block';

                            finalPrice.innerHTML = this.config.regular.formatted_price;

                            this.$emitter.emit('configurable-variant-selected-event', 0);
                        }
                    },

                    reloadImages () {
                        galleryImages.splice(0, galleryImages.length)

                        if (this.possibleOptionVariant) {
                            this.config.variant_images[this.possibleOptionVariant].forEach(function(image) {
                                galleryImages.push(image);
                            });

                            this.config.variant_videos[this.possibleOptionVariant].forEach(function(video) {
                                galleryImages.push(video);
                            });
                        }

                        this.galleryImages.forEach(function(image) {
                            galleryImages.push(image);
                        });

                        if (galleryImages.length) {
                            this.$parent.$parent.$refs.gallery.media.images =  [...galleryImages];
                        }

                        this.$emitter.emit('configurable-variant-update-images-event', galleryImages);
                    },
                }
            });

        </script>
    @endpush

@endif
