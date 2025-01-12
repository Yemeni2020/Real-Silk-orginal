<div class="modal fade" id="ServiceNowModal" tabindex="-1" role="dialog" aria-labelledby="ServiceNowModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ServiceNowModalLabel">{{ translate('add_New_Product') }}</h5>
                <button type="button" onclick="$('#itemTableBody').html(''); " class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addItemForm">
                    <div class="row">
                        <div class="col-md-6" id="dvitemName">
                        <!-- اسم العنصر -->
                            <div class="form-group">
                                <label for="itemName">{{ translate('item_Name') }}</label>
                                <input type="text" class="form-control" id="itemName" placeholder="{{ translate('enter_Item_Name') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6" id="dvitemType">
                            <!-- نوع العنصر -->
                            <div class="form-group">
                                <label for="itemType">{{ translate('item_Type') }}</label>
                                <select class="form-control" onchange="Select_type()" id="itemType" required>
                                    <option value="">{{ translate('select_Type') }}</option>
                                    <option value="text">{{ translate('text') }}</option>
                                    <option value="date">{{ translate('date') }}</option>
                                    <option value="email">{{ translate('email') }}</option>
                                    <option value="select">{{ translate('select') }}</option>
                                    <option value="number">{{ translate('number') }}</option>
                                    <option value="radios">{{ translate('radios') }}</option>
                                    <option value="checkbox">{{ translate('checkbox') }}</option>
                                    <option value="h1">{{ translate('h1') }}</option>
                                    <option value="h2">{{ translate('h2') }}</option>
                                    <option value="h3">{{ translate('h3') }}</option>
                                    <option value="h4">{{ translate('h4') }}</option>
                                    <option value="h5">{{ translate('h5') }}</option>
                                    <option value="h6">{{ translate('h6') }}</option>
                                    <option value="hr">{{ translate('hr') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 inpt" style="display:none;" id="dvitemOrder">
                            <!-- ترتيب العنصر -->
                            <div class="form-group">
                                <label for="itemOrder">{{ translate('item_Order') }}</label>
                                <input type="number" value="0" class="form-control" id="itemOrder" placeholder="{{ translate('enter_Item_Order') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6 inpt" style="display:none;" id="dvisRequired">
                            <!-- هل هو إجباري -->
                            <div class="form-group">
                                <label for="isRequired">{{ translate('is_Required') }}</label>
                                <select class="form-control" id="isRequired" required>
                                    <option value="1">{{ translate('yes') }}</option>
                                    <option value="0" selected>{{ translate('no') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6"  style="display:none;"id="dvlength">
                            <!-- الطول -->
                            <div class="form-group" >
                                <label for="itemLength">{{ translate('item_Length') }}</label>
                                <input type="number" value="255" class="form-control" id="itemLength" placeholder="{{ translate('enter_Item_Length') }}">
                            </div>
                        </div>
                        <div class="col-md-6"  style="display:none;"id="dvdefaultValue">
                            <!-- القيمة الافتراضية -->
                            <div class="form-group">
                                <label for="defaultValue">{{ translate('default_Value') }}</label>
                                <input type="text" class="form-control" id="defaultValue" placeholder="{{ translate('enter_Default_Value') }}">
                            </div>
                        </div>
                    </div>
                    

                    

                    <div id="AddItem_Select" style="display: none;">
                        <div class="row">
                            <!-- إضافة عنصر -->
                            <div class="form-row align-items-center mb-4">
                                <div class="col-md-8">
                                    <input type="text" class="form-control" id="itemSelectInput" placeholder="{{ translate('enter_Item_select') }}">
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-primary btn-block" onclick="addItemSelectButton2();" id="addItemSelectButton">{{ translate('add') }}</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- الجدول -->
                        <table class="table table-hover table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ translate('Select_Name') }}</th>
                                    <th>{{ translate('actions') }}</th>
                                </tr>
                            </thead>
                            <tbody id="itemTableBody">
                                <!-- الصفوف المضافة ديناميكيًا -->
                            </tbody>
                        </table>

                    </div>
                    



                    <div class="modal-footer">
                        <input type="hidden" id="IndexFaild" >
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('close') }}</button>
                        <button type="button" id="btn-add" onclick="addFaildItem()" class="btn btn-primary">{{ translate('save') }}</button>
                        <button type="button" id="btn-edit" onclick="editFaildItem($('#IndexFaild').val())" class="btn btn-primary" >{{ translate('save') }}</button>
                    </div>
                    
                </form>
            </div>
        </div>
    </div>
</div>