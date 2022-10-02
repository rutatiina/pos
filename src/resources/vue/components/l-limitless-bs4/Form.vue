<template>

    <!-- Main content -->
    <div class="content-wrapper">


        <div class="d-table">
            <div class="d-table-cell align-top pl-1">

                <!-- Page header -->
                <div class="page-header">
                    <div class="page-header-content header-elements-md-inline">
                        <div class="page-title d-flex">
                            <h4>
                                <i class="icon-file-plus"></i>
                                {{pageTitle}}
                            </h4>
                        </div>

                    </div>

                    <div class="breadcrumb-line header-elements-md-inline">
                        <div class="d-flex">
                            <div class="breadcrumb">
                                <a href="/" class="breadcrumb-item">
                                    <i class="icon-home2 mr-2"></i>
                                    <span class="badge badge-primary badge-pill font-weight-bold rg-breadcrumb-item-tenant-name"> {{this.$root.tenant.name | truncate(30) }} </span>
                                </a>
                                <span class="breadcrumb-item">Cash sales</span>
                                <span class="breadcrumb-item active">{{pageAction}}</span>
                            </div>

                            <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                        </div>

                        <div class="header-elements">
                            <div class="breadcrumb justify-content-center">
                                <router-link to="/cash-sales" class=" btn btn-danger btn-sm rounded-round font-weight-bold">
                                    <i class="icon-drawer3 mr-2"></i>
                                    Cash sales
                                </router-link>
                            </div>
                        </div>

                    </div>

                </div>
                <!-- /page header -->

                <div class="row">
                    <div v-for="(item, index) in txnItems"
                         v-if="item.name != ''"
                         class="col-xl-3 col-sm-6">
                        <div class="card rounded-0">
                            <div class="card-body p-0">
                                <div class="card-img-actions">
                                    <a href="/template/l/global_assets/images/placeholders/placeholder.jpg" data-popup="lightbox">
                                        <img v-if="item.image_url" :src="item.image_url" class="card-img" width="96" alt="">
                                        <img v-else src="/template/l/global_assets/images/placeholders/placeholder.jpg" class="card-img" width="96" alt="">
                                        <span class="card-img-actions-overlay card-img">
                                            <i class="icon-plus3 icon-2x"></i>
                                        </span>
                                    </a>
                                </div>
                            </div>

                            <div class="card-body bg-light text-center">
                                <div class="mb-2">
                                    <h6 class="font-weight-semibold mb-0">
                                        <a href="#" class="text-default">{{item.name}}</a>
                                    </h6>

                                    <!--<a href="#" class="text-muted">Men's Accessories</a>-->
                                </div>

                                <h3 class="font-weight-semibold">${{rgNumberFormat(item.rate)}}</h3><!--mb-0 -->

                                <!--<div>-->
                                <!--    <i class="icon-star-full2 font-size-base text-warning-300"></i>-->
                                <!--    <i class="icon-star-full2 font-size-base text-warning-300"></i>-->
                                <!--    <i class="icon-star-full2 font-size-base text-warning-300"></i>-->
                                <!--    <i class="icon-star-full2 font-size-base text-warning-300"></i>-->
                                <!--    <i class="icon-star-full2 font-size-base text-warning-300"></i>-->
                                <!--</div>-->

                                <!--<div class="text-muted mb-3">0 reviews</div>-->

                                <button type="button" class="btn bg-teal-400" @click="addToCart(item)"><i class="icon-cart-add mr-2"></i> Add to cart</button>
                            </div>
                        </div>
                    </div>
                </div>



            </div>
            <div class="d-table-cell" style="width: 500px;">


                <!-- Form horizontal -->
                <perfect-scrollbar ref="scroll" class="card shadow-none rounded-0 border-0 fixed-top position-fixed h-100 overflow-hidden" style="width: 500px; right: 0; top:50px; left: auto !important; bottom: 0px;">

                    <div class="card-body">

                        <loading-animation></loading-animation>

                        <form v-if="!this.$root.loading"
                              @submit="txnFormSubmit"
                              action=""
                              method="post"
                              class=""
                              style="margin-bottom: 100px;"
                              autocomplete="off">


                            <input type="hidden" name="submit" value="1" />
                            <input type="hidden" name="id" :value="txnAttributes.id" />
                            <input type="hidden" name="contact_name" value="" />
                            <input type="hidden" name="internal_ref" :value="txnAttributes.internal_ref" />
                            <input type="hidden" name="quote_currency" :value="$root.tenant.base_currency" />

                            <fieldset id="fieldset_select_contact" class="select_contact_required">

                                <div class="form-group row">
                                    <div class="col-12">
                                        <ul class="list list-unstyled mb-0">
                                            <li>
                                                <h5 class="rg-font-weight-600">{{$root.tenant.name}}</h5>
                                            </li>
                                            <li v-if="$root.tenant.street_line_1 || $root.tenant.street_line_2"><small class="text-muted">Street</small> {{$root.tenant.street_line_1}} {{$root.tenant.street_line_2}}</li>
                                            <li v-if="$root.tenant.city"><small class="text-muted">City</small> {{$root.tenant.city}}</li>
                                            <li v-if="$root.tenant.phone"><small class="text-muted">Phone no.</small> {{$root.tenant.phone}}</li>
                                            <li v-if="$root.tenant.website"><small class="text-muted">Website</small> {{$root.tenant.website}}</li>
                                        </ul>
                                    </div>

                                </div>

                            </fieldset>

                            <fieldset>

                                <div class="form-group row">
                                    <div class="col-12">
                                        Receipt No.: {{txnAttributes.number}}
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-12">
                                        Date: {{txnAttributes.date}}
                                    </div>
                                </div>

                            </fieldset>
                            <!--<div class="max-width-1040 clearfix ml-20" style="border-bottom: 1px solid #ddd;"></div>-->

                            <fieldset class="">
                                <div class="form-group">
                                    <hr class="m-0 p-0">
                                    <table class="table">
                                        <thead class="thead-default">
                                        <tr>
                                            <th class="col font-weight-bold pl-2 text-uppercase">Item</th>
                                            <th class="col-auto font-weight-bold pl-2 text-uppercase">Qty</th>
                                            <th class="col-auto font-weight-bold text-right text-uppercase">Price</th>
                                            <th class="col-auto font-weight-bold text-right pr-0 text-uppercase">Amount</th>
                                            <th class="col-auto font-weight-bold p-0">
                                                <button type="button"
                                                        @click="txnItemsClearAll"
                                                        class="btn bg-danger bg-transparent text-danger btn-icon"
                                                        title="Clear all items">
                                                    <i class="icon-cross3"></i>
                                                </button>
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        <tr v-for="(item, index) in txnAttributes.items">
                                            <td class="td_item_selector pl-2 rg_select2_border_none">
                                                {{item.name}}
                                            </td>
                                            <td class="pl-2">
                                                {{rgNumberFormat(item.quantity)}}
                                            </td>
                                            <td class="p-0 text-right">
                                                {{rgNumberFormat(item.rate)}}
                                            </td>
                                            <td class="pr-0 text-right">{{rgNumberFormat(item.displayTotal)}}</td>
                                            <td class="p-0">
                                                <button type="button"
                                                        @click="txnItemsRemove(index)"
                                                        class="btn bg-danger bg-transparent text-danger btn-icon"
                                                        title="Delete row">
                                                    <i class="icon-cross3"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        </tbody>

                                        <!--<tbody>-->
                                        <!--<tr class="rg-font-size-totals">-->
                                        <!--    <td class="pl-15 border-left-0 border-top-0 border-right-0 font-weight-bold" colspan="2">Sub Total</td>-->
                                        <!--    <td class="border-left-0 border-top-0 border-right-0 text-right pr-0" colspan="2">{{rgNumberFormat(txnAttributes.taxable_amount, 2)}}</td>-->
                                        <!--    <td class="p-0 border-left-0 border-top-0 border-right-0 "></td>-->
                                        <!--</tr>-->
                                        <!--</tbody>-->

                                        <tbody class="border-0">

                                        <tr v-for="tax in txnAttributes.taxes" class="rg-font-size-totals">
                                            <td class="pl-2 border-left-0 border-top-0 border-right-0 font-weight-bold" colspan="2">{{tax.name}}</td>
                                            <td class="border-left-0 border-top-0 border-right-0 text-right pr-0" colspan="2">
                                                <!--{{rgNumberFormat(tax.total, 2, '.', '')}}-->
                                                <div class="input-group">
                                                    <input type="text"
                                                           v-model="tax.total"
                                                           class="rg-txn-item-row-total form-control text-right"
                                                           placeholder="0.00">
                                                    <span class="input-group-append border-0 rounded-0">
                                                    <button type="button"
                                                            @click="txnItemsTaxRemove(tax.id)"
                                                            class="btn bg-danger bg-transparent text-danger btn-icon"
                                                            title="Remove Tax">
                                                        <i class="icon-cross3"></i>
                                                    </button>
                                                </span>
                                                </div>
                                            </td>
                                            <td class="p-0 border-left-0 border-top-0 border-right-0 "></td>
                                        </tr>

                                        </tbody>

                                        <tfoot>

                                        <tr class="rg-font-size-totals">
                                            <td class="pl-2 border-left-0 border-top-0 border-right-0 size4of5" colspan="2">
                                                <span class="font-weight-semibold">TOTAL</span>
                                                <!--<span v-if="txnAttributes.base_currency">-->
                                                <!--    {{txnAttributes.base_currency}}-->
                                                <!--</span>-->
                                            </td>
                                            <td class="border-left-0 border-top-0 border-right-0 font-weight-semibold size4of5 text-right pl-0" colspan="3">{{rgNumberFormat(txnAttributes.total)}}</td>
                                            <!--<td class="p-0 border-left-0 border-top-0 border-right-0"></td>-->
                                        </tr>

                                        </tfoot>

                                    </table>
                                </div>
                            </fieldset>

                            <div class="form-group row d-none">
                                <label class="col-form-label col-lg-2">Attach files</label>
                                <div class="col-lg-10">
                                    <input ref="filesAttached" type="file" multiple class="form-control border-0 p-1 h-auto">
                                </div>
                            </div>

                            <div class="text-left col-12 p-0 rg-fixed-bottom-right">

                                <div class="btn-group ml-1">
                                    <button type="button" class="btn btn-outline bg-purple-300 border-purple-300 text-purple-800 btn-icon border-2 dropdown-toggle" data-toggle="dropdown">
                                        <i class="icon-cog"></i>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-left">
                                        <a href="/" class="dropdown-item" v-on:click.prevent="txnOnSave('draft', false)">
                                            <i class="icon-file-text3"></i> Save as draft
                                        </a>
                                        <a href="/" class="dropdown-item" v-on:click.prevent="txnOnSave('approved', false)">
                                            <i class="icon-file-check2"></i> Save and approve
                                        </a>
                                        <a href="/" class="dropdown-item" v-on:click.prevent="txnOnSave('approved', true)">
                                            <i class="icon-mention"></i> Save, approve and send
                                        </a>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-danger font-weight-bold">
                                    <i class="icon-file-plus2 mr-1"></i> {{txnSubmitBtnText}}
                                </button>

                            </div>

                        </form>

                    </div>

                </perfect-scrollbar>
                <!-- /form horizontal -->

            </div>
        </div>

        <!-- Content area -->
        <div class="content border-0 padding-0">




        </div>
        <!-- /content area -->

    </div>
    <!-- /main content -->

</template>

<style>
    .rg-font-size-totals {
        font-size: 24px !important;
    }
    .rg-fixed-bottom-right {
        position: fixed;
        top: auto;
        right: auto;
        bottom: 10px;
        left: calc(100% - 480px);;
        z-index: 1030;
    }
</style>

<script>

    export default {
        components: {},
        data() {
            return {}
        },
        created: function () {
            this.pageTitle = 'Create RecurringBill'
            this.pageAction = 'Create'
        },
        mounted() {

            //console.log(this.$route.fullPath)
            this.txnCreateData()
            this.txnFetchSuppliers('-initiate-')
            this.txnFetchItems('-initiate-')
            this.txnFetchTaxes()
            //this.txnFetchAccounts()
        },
        methods: {
            addToCart(item)
            {
                //console.log('txnItemsRate', itemData)
                if (typeof item.rate !== 'undefined')
                {
                    //loop through the items already assed and check if the item selected already exixts
                    //if it exixts, only increase quantiy otherwise add the item
                    // this.txnAttributes.items.forEach((value) => {
                    for (const prop in this.txnAttributes.items) {
                        if (item.id === this.txnAttributes.items[prop].id)
                        {
                            this.txnAttributes.items[prop].quantity += 1;
                            return true;
                        }
                    };

                    console.log('called')

                    this.txnAttributes.items.push({
                        id: item.id,
                        selectedItem: {},
                        selectedTaxes: [],
                        type: '',
                        type_id: item.id,
                        contact_id: '',
                        name: item.name,
                        description: item.description,
                        rate: item.rate,
                        quantity: 1,
                        total: item.rate,
                        taxes: [],
                        tax_id: '',
                        units: '',
                        batch: '',
                        expiry: '',
                    });

                    this.txnTotal();

                    this.$refs.scroll.$el.scrollTop = this.$refs.scroll.$el.scrollHeight;;
                }
            }
        },
        beforeUpdate: function () {
            //
        },
        updated: function () {},
        destroyed: function () {
            //
        }
    }
</script>
