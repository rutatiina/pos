<template>

    <!-- Main content -->
    <div class="content-wrapper">

        <!-- Page header -->
        <div class="page-header page-header-light d-print-none">
            <div class="page-header-content header-elements-md-inline">
                <div class="page-title d-flex">
                    <h4><i class="icon-files-empty2 mr-2"></i> <span class="font-weight-semibold">Recurring Bill</span></h4>
                    <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                </div>

            </div>

            <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                <div class="d-flex">
                    <div class="breadcrumb">
                        <a href="/" class="breadcrumb-item">
                            <i class="icon-home2 mr-2"></i>
                            <span class="badge badge-primary badge-pill font-weight-bold rg-breadcrumb-item-tenant-name"> {{this.$root.tenant.name | truncate(30) }} </span>
                        </a>
                        <span class="breadcrumb-item">Accounting</span>
                        <span class="breadcrumb-item">Purchases</span>
                        <span class="breadcrumb-item active">Recurring Bill</span>
                    </div>

                    <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                </div>

                <div class="header-elements">
                    <div class="breadcrumb justify-content-center">
                        <router-link to="/recurring-bills/create"
                                     class="btn btn-outline btn-primary border-primary text-primary-800 border-2 btn-sm rounded font-weight-bold mr-2">
                            <i class="icon-files-empty2 mr-1"></i>
                            New Recurring Bill
                        </router-link>

                        <button type="button" @click="$root.showSideBarRight=true" class="btn btn-outline btn-primary border-primary text-primary-800 border-2 btn-sm rounded btn-icon" >
                            <i class="icon-cog52"></i>
                        </button>

                    </div>
                </div>
            </div>
        </div>
        <!-- /page header -->


        <!-- Content area -->
        <div class="content border-0 p-0">

            <loading-txn-animation></loading-txn-animation>

            <!-- Content area -->
            <div class="content" v-if="!this.$root.loadingTxn && txnData">

                <div class="card rounded-0 max-width-960 ml-auto mr-auto rg-print-border-0">

                    <div class="card-body">
                        <!--<h6 class="card-title">-->
                        <!--    <i class="mi-timer mr-2"></i> Next due date time:-->
                        <!--    <span class="font-weight-bold">{{txnData.recurring.next_run}}</span>-->
                        <!--</h6>-->
                        <p class="card-text">
                            <i class="mi-info-outline mr-2"></i> Click on the status to change by choosing an option from the dropdown menu that appears
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-sm-12 col-md-4 border-right-1 border-light">
                                <p><i class="icon-pulse2 icon-2x d-inline-block text-info"></i></p>
                                <!--<h5 class="font-weight-semibold mb-0 text-capitalize">{{txnData.recurring.frequency}}</h5>-->
                                <div class="justify-content-center">
                                    <div class="dropdown-toggle font-weight-semibold h5 mb-0 text-capitalize cursor-pointer" data-toggle="dropdown">{{txnData.recurring.frequency}}</div>

                                    <div class="dropdown-menu dropdown-menu-center" v-if="txnData.recurringOptions.frequency">
                                        <a v-for="frequency in txnData.recurringOptions.frequency"
                                           href="#" class="dropdown-item" v-on:click.prevent @click="txnData.recurring.frequency = frequency.value">{{frequency.text}}</a>
                                    </div>
                                </div>
                                <span class="text-muted font-size-sm">Frequency</span>
                            </div>

                            <div class="col-sm-12 col-md-4 border-right-1 border-light">
                                <p><i class="icon-calendar2 icon-2x d-inline-block text-warning"></i></p>
                                <h5 class="font-weight-semibold mb-0">{{txnData.recurring.start_date}} to {{txnData.recurring.end_date}}</h5>
                                <span class="text-muted font-size-sm">Date range</span>
                            </div>

                            <div class="col-sm-12 col-md-4">
                                <p><i class="icon-cash3 icon-2x d-inline-block text-success"></i></p>
                                <!--<h5 class="font-weight-semibold mb-0 text-capitalize">{{txnData.recurring.status}}</h5>-->

                                <div class="justify-content-center">
                                    <div class="dropdown-toggle font-weight-semibold h5 mb-0 text-capitalize cursor-pointer" data-toggle="dropdown">{{txnData.recurring.status}}</div>

                                    <div class="dropdown-menu dropdown-menu-center">
                                        <a href="#" class="dropdown-item" v-on:click.prevent @click="txnData.recurring.status = 'active'">Active</a>
                                        <a href="#" class="dropdown-item" v-on:click.prevent @click="txnData.recurring.status = 'paused'">Paused</a>
                                        <a href="#" class="dropdown-item" v-on:click.prevent @click="txnData.recurring.status = 'de-activated'">De-activated</a>
                                    </div>
                                </div>

                                <span class="text-muted font-size-sm">Status</span>
                            </div>
                        </div>

                        <div class="row text-center mt-3" v-if="UpdateRecurringConfiguration">
                            <div class="col-12">
                                <button type="button"
                                        @click="RecurringPropertiesUpdate"
                                        class="btn btn-primary btn-labeled btn-labeled-left font-weight-bold">
                                    <b><i class="mi-save"></i></b> Update recurring Properties
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <span class="text-muted"><i class="mi-info-outline mr-2"></i> This recurring transaction is automatically run on the due time.</span>
                    </div>
                </div>

                <!-- txn template -->
                <div v-if="txnData.status === 'draft'"
                     class="card border-left-3 border-warning rounded-0 max-width-960 ml-auto mr-auto rg-print-border-0">
                    <div class="card-header header-elements-inline d-print-none text-danger">
                        <h6 class="card-title font-weight-bold">
                            Approve {{txnData.document_name}}<br>
                            <small>You are viewing a draft</small>
                        </h6>
                        <div class="header-elements">
                            <button type="button"
                                    class="btn bg-warning font-weight-bold"
                                    @click="txnApprove('/recurring-bills/'+txnData.id+'/approve')"><i class="icon-printer mr-2"></i> Click here to Approve</button>
                        </div>
                    </div>
                </div>

                <div class="card max-width-960 m-auto rg-print-border-0" v-if="!this.$root.loadingTxn">

                    <div class="card-header bg-transparent header-elements-inline d-print-none">
                        <h6 class="card-title">{{txnData.document_name}} #{{txnData.number}}</h6>
                        <div class="header-elements">

                            <router-link :to="'/recurring-bills/'+$route.params.id+'/copy'"
                                         class="btn btn-light btn-sm">
                                <i class="icon-copy"></i>
                                Copy
                            </router-link>

                            <button type="button" class="btn btn-light btn-sm ml-3" onclick="window.print();"><i class="icon-printer mr-2"></i> Print</button>

                        </div>
                    </div>

                    <div class="card-body">

                        <div class="row"
                             v-if="$root.tenant.logo">
                            <div class="col-sm-6 mb-2">
                                <img :src="'/timthumb.php?src=storage/' + $root.tenant.logo + '&h=27&q=100'" class="" :alt="$root.tenant.name" >
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <ul class="list list-unstyled mb-0">
                                        <li>
                                            <h5 class="rg-font-weight-600">{{$root.tenant.name}}</h5>
                                        </li>
                                        <li v-if="$root.tenant.street_line_1"><small class="text-muted">Street #1</small> {{$root.tenant.street_line_1}}</li>
                                        <li v-if="$root.tenant.street_line_2"><small class="text-muted">Street #2</small> {{$root.tenant.street_line_2}}</li>
                                        <li v-if="$root.tenant.city"><small class="text-muted">City</small> {{$root.tenant.city}}</li>
                                        <li v-if="$root.tenant.state_province"><small class="text-muted">Province</small> {{$root.tenant.state_province}}</li>
                                        <li v-if="$root.tenant.phone"><small class="text-muted">Phone no.</small> {{$root.tenant.phone}}</li>
                                        <li v-if="$root.tenant.website"><small class="text-muted">Website</small> {{$root.tenant.website}}</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <div class="text-sm-right">
                                        <h4 class="text-primary mb-2 mt-md-2">{{txnData.document_name}} #{{txnData.number}}</h4>
                                        <ul class="list list-unstyled mb-0">
                                            <li>Date: <span class="font-weight-semibold">{{txnData.date}}</span></li>
                                            <li v-if="txnData.due_date">Due date: <span class="font-weight-semibold">{{txnData.due_date}}</span></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-md-flex flex-md-wrap">
                            <div class="mb-4 mb-md-2">
                                <span class="text-muted">{{txnData.document_name}} To:</span>
                                <ul v-if="txnData.contact" class="list list-unstyled mb-0">
                                    <li><h5 class="my-2">{{txnData.contact.contact_salutation}} {{txnData.contact_name}}</h5></li>
                                    <li v-if="txnData.contact.shipping_address_street1 && txnData.contact.shipping_address_street2">
                                        <span class="font-weight-semibold">{{txnData.contact.shipping_address_street1}} {{txnData.contact.shipping_address_street2}}</span>
                                    </li>
                                    <li v-if="txnData.contact.shipping_address_city">{{txnData.contact.shipping_address_city}}</li>
                                    <li v-if="txnData.contact.shipping_address_state">{{txnData.contact.shipping_address_state}}</li>
                                    <li v-if="txnData.contact.shipping_address_country">{{txnData.contact.shipping_address_country}}</li>
                                    <li v-if="txnData.contact.contact_work_phone">{{txnData.contact.contact_work_phone}}</li>
                                    <li v-if="txnData.contact.contact_email"><a href="#">{{txnData.contact.contact_email}}</a></li>
                                </ul>
                            </div>

                            <div class="mb-2 ml-auto">
                                <span class="text-muted">Summary:</span>
                                <div class="d-flex flex-wrap wmin-md-400">
                                    <ul class="list list-unstyled mb-0">
                                        <li><h5 class="my-2">Total Due:</h5></li>
                                        <li v-if="txnData.reference">Reference:</li>
                                    </ul>

                                    <ul class="list list-unstyled text-right mb-0 ml-auto">
                                        <li><h5 class="font-weight-semibold my-2">{{rgNumberFormat(txnData.total, 2)}}</h5></li>
                                        <li v-if="txnData.reference">{{txnData.reference}}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-lg">
                            <thead>
                            <tr class="bg-light">
                                <th class="font-weight-bold">Description</th>
                                <th class="font-weight-bold text-right">Rate</th>
                                <th class="font-weight-bold text-right">Quantity</th>
                                <th class="font-weight-bold text-right">Total <small> {{txnData.base_currency}}</small></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="item in txnData.items"
                                v-if="!['txn', 'txn_type', 'tax'].includes(item.type)">
                                <td>
                                    <h6 class="mb-0">{{item.name}}</h6>
                                    <span class="">{{item.description}}</span>
                                </td>
                                <td class="text-right">{{rgNumberFormat(item.rate, 2)}}</td>
                                <td class="text-right">{{rgNumberFormat(item.quantity)}}</td>
                                <td class="text-right">
                                    <span class="font-weight-semibold">{{rgNumberFormat(item.total, 2)}}</span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="card-body pr-0">
                        <div class="d-md-flex flex-md-wrap">
                            <div class="pt-2 mb-3 text-muted">
                                <h6>Authorized Stamp / Signature</h6>
                            </div>

                            <div class="mb-3 wmin-md-400 ml-auto">
                                <div class="table-responsive">
                                    <table class="table">
                                        <tbody>
                                        <tr>
                                            <th>Subtotal:</th>
                                            <td class="text-right">{{rgNumberFormat(txnData.taxable_amount, 2)}}</td>
                                        </tr>

                                        <tr v-for="item in txnData.items"
                                            v-if="['txn_type', 'tax'].includes(item.type)">
                                            <th>{{item.description}}</th>
                                            <td class="text-right">{{rgNumberFormat(item.total, $root.tenant.decimal_places)}}</td>
                                        </tr>

                                        <tr>
                                            <th>Total:</th>
                                            <td class="text-right">
                                                <span>{{txnData.base_currency}}</span>
                                                <span class="h5 font-weight-semibold">{{rgNumberFormat(txnData.total, 2)}}</span>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!--<div class="text-right mt-3 mr-3 d-print-none">-->
                                <!--    <button type="button"-->
                                <!--            class="btn btn-primary btn-labeled btn-labeled-left">-->
                                <!--        <b><i class="icon-paperplane"></i></b> Send {{txnData.document_name}}-->
                                <!--    </button>-->
                                <!--</div>-->

                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h6>Amount in words:</h6>
                        <p class="text-muted">{{txnData.total_in_words}}</p>
                    </div>

                    <div class="card-footer">
                        <span class="text-muted">Thank you for working with us. Always contact us for any feedback.</span>
                    </div>
                </div>
                <!-- /invoice template -->

            </div>
            <!-- /content area -->

        </div>
        <!-- /content area -->


        <!-- Footer -->
        <financial-accounting-footer></financial-accounting-footer>
        <!-- /footer -->

    </div>
    <!-- /main content -->

</template>

<style>
    .dropdown-menu-center {
        right: auto !important;;
        left: 50% !important;;
        -webkit-transform: translate(-50%, 68px) !important;;
        -o-transform: translate(-50%, 68px) !important;;
        transform: translate(-50%, 68px) !important;
    }
</style>

<script>

    import FinancialAccountingFooter from '../../../../../financial-accounting/resources/vue/components/l-limitless-bs4/Footer'

    export default {
        components: {
            FinancialAccountingFooter
        },
        data() {
            return {
                UpdateRecurringConfiguration: false
            }
        },
        watch: {
            $route (to, from) {
                if (this.txnShowId !== this.$route.params.id) this.txnFetchData()
            },
            'txnData.recurring.frequency': function (newValue, oldValue)
            {
                if (typeof oldValue == 'undefined') return false;

                this.RecurringPropertiesOnChange();

                // if (newValue === this.txnDataOriginal.recurring.frequency)
                // {
                //     //do not show Or hide the save / update button
                //     console.log('the value has not been changed from: ' + this.txnDataOriginal.recurring.frequency)
                // }
                // else
                // {
                //     this.UpdateRecurringConfiguration = true;
                //     console.log('newValue: '+newValue+', oldValue: '+oldValue)
                //     console.log('the recurring frequency has changed')
                // }
            },
            'txnData.recurring.status': function (newValue, oldValue)
            {
                this.RecurringPropertiesOnChange();
            }
        },
        mounted() {
            this.$root.appMenu('accounting')

            this.txnFetchData() //get the details of the transaction

            this.txnShowId = this.$route.params.id

            // console.log(this.$route);

        },
        methods: {
            RecurringPropertiesOnChange()
            {
                if (JSON.stringify(this.txnData.recurring) !== JSON.stringify(this.txnDataOriginal.recurring))
                {
                    // console.log('*** txnData.recurring configs have changed')

                    this.UpdateRecurringConfiguration = true;
                }
                else
                {
                    this.UpdateRecurringConfiguration = false;
                }
            },
            RecurringPropertiesUpdate(e) {

                e.preventDefault();

                let currentObj = this;

                PNotify.removeAll();

                let PNotifySettings = JSON.parse(JSON.stringify(this.$root.PNotifySettings));

                let notice = new PNotify(PNotifySettings);

                axios.patch(
                    '/recurring-bills/recurring/'+this.txnData.id+'/properties/'+this.txnData.recurring.id,
                    this.txnData.recurring
                )
                    .then(function (response) {

                        //PNotify.removeAll();
                        // console.log(response.data);
                        // console.log(PNotifySettings);

                        PNotifySettings.text = response.data.messages.join("\n");

                        if (response.data.status === true)
                        {
                            PNotifySettings.title = 'Success';
                            PNotifySettings.type = 'success';
                            PNotifySettings.addclass = 'bg-success-400 border-success-400';

                            notice.update(PNotifySettings);
                            currentObj.UpdateRecurringConfiguration = false;
                            currentObj.txnDataOriginal.recurring = response.data.recurringProperties;
                        }
                        else
                        {
                            PNotifySettings.title = '! Error';
                            PNotifySettings.type = 'error';
                            PNotifySettings.addclass = 'bg-warning-400 border-warning-400';

                            notice.update(PNotifySettings);
                        }

                        notice.get().click(function () {
                            notice.remove();
                        });

                    })
                    .catch(function (error) {
                        //currentObj.response = error;
                    });
            },
        },
        ready:function(){},
        beforeUpdate: function () {},
        updated: function () {
            this.txnShowId = this.$route.params.id;
            document.title = this.txnData.document_name + ' ' + this.txnData.number;
        }
    }
</script>
