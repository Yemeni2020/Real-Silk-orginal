
var index=0;
function Select_type(){
    var itemType=$("#itemType").val();
    // alert(itemType);
    var input=false;



    if(itemType =="text" || itemType =="date" ||itemType =="email"||itemType =="select" ||itemType =="number" ||itemType =="radios" ||itemType =="checkbox") {
        input=true;
    }
    if(input){
        
        $('.inpt').show();
        if(itemType == "select" || itemType == "radios" || itemType == "checkbox"){
            // alert(":ds22");

            $("#AddItem_Select").show();

            $("#dvdefaultValue").hide();
            $("#dvlength").hide();
        }else{
            $("#dvdefaultValue").show();
            $("#dvlength").show();
            $("#AddItem_Select").hide();

        }

    }else{
        $("#dvdefaultValue").hide();
        $("#dvlength").hide();
        $("#AddItem_Select").hide();
    }
}


function addFaildItem(){
    
    
   if($("#itemType").val()=="select" || $("#itemType").val()=="radios" || $("#itemType").val()=="checkbox"){
        if($("#itemTableBody").html()==""){
            showToast("Error: Must Add Option On Type Faild "+$("#itemType").val(), "error"); // عرض رسالة الخطأ باستخدام Toast
            return;
        }
    }
    // const itemName = document.getElementById("itemName").value;
    const itemType = document.getElementById("itemType").value;
    const itemOrder = document.getElementById("itemOrder").value;
    const isRequired = document.getElementById("isRequired").value;
    const itemLength = document.getElementById("itemLength").value;
    const defaultValue = document.getElementById("defaultValue").value;

    var selectOptions = [];
    const itemName = [];

    var value=``;
    // alert(defaultValue);
    var lang=$(".lang-input");
    var len=$(".lang-input").length;
    
    for (let i = 0; i < len; i++) {
        selectOptions = [];
        // const element = array[i];
        // alert($(lang[i]).val());

        if($("#itemTableBody"+$(lang[i]).val()+" tr").length==0){
            if($("#itemType").val()=="select" || $("#itemType").val()=="radios" || $("#itemType").val()=="checkbox"){
                if($("#itemTableBody"+$(lang[0]).val()+" tr").length==0 ){
                    showToast("Error: Must Enter All Select Fields", "error"); // عرض رسالة الخطأ باستخدام Toast
                    return;
                }
                else{
                    document.querySelectorAll("#itemTableBody"+$(lang[0]).val()+" tr").forEach((row) => {
                        var selectName = row.querySelector("td:first-child").textContent;
                        // alert(selectName);
                        selectOptions.push(selectName);
                        
                    });
                }
            }
        }
        document.querySelectorAll("#itemTableBody"+$(lang[i]).val()+" tr").forEach((row) => {
            var selectName = row.querySelector("td:first-child").textContent;
            // alert(selectName);
            

                selectOptions.push(selectName);
            
        });
        var itemname2=$(".itemName22");

        // alert($(itemname2[i]).val());
        if($(itemname2[i]).val()=="" || $(itemname2[i]).val() ==undefined || $(itemname2[i]).val()==null){
            itemName.push($(itemname2[0]).val());
        }
        else{
            itemName.push($(itemname2[i]).val());
        }
        value+=`<input type='hidden' name='item[selectName${$(lang[i]).val()}][][]' value="`+selectOptions+`" />`;
    }
    


    if(itemName[0]=="" || itemName[0]=="" || itemName[0]==null || itemName[0]==undefined){
        showToast("Error: Must Enter All Fields ", "error"); // عرض رسالة الخطأ باستخدام Toast
        return;
    }

    // alert("d");
    const html = `
    <tr id='faild${index}'>
        <td>${itemName[0]}</td>
        <td>${itemType}</td>
        <td>
            <button onclick="openformedit(${index},'${itemName}','${itemType}','${itemOrder}','${isRequired}','${itemLength}','${defaultValue}','${selectOptions}');" type="button" class="btn btn-primary" data-toggle="modal" data-target="#ServiceNowModal"><i class="tio-edit"></i></button>
            <button onclick='deleteFaildItem(${index})' type="button" class="btn btn-danger"><i class="tio-delete"></i></button>
        </td>
        <input type="hidden"  name="item[Name][]" value="${itemName}" />
        <input type="hidden"  name="item[Type][]" value="${itemType}" />
        <input type="hidden" name="item[Order][]" value="${itemOrder}" />
        <input type="hidden" name="item[isRequired][]" value="${isRequired}" />
        <input type="hidden"  name="item[Length][]" value="${itemLength}" />
        <input type="hidden"name="item[defaultValue][]" value="${defaultValue}" />
        ${value}
    </tr>`;

    $('#ServiceNowModal').modal('hide');

    $("#faild_form").append(html);
    index++;
    document.getElementById("itemName").value="";
    $("#itemTableBody").html(''); 

}


function openformedit(idx, itemName, itemType, itemOrder, isRequired, itemLength, defaultValue, selectOptions) {
    $('#btn-edit').show(); $('#btn-add').hide();
    $("#IndexFaild").val(idx);


    // $("#itemName").val(itemName);
    $("#itemType").val(itemType).trigger('change'); // لتحديث الحقول المعتمدة على التغيير
    $("#itemOrder").val(itemOrder);
    $("#isRequired").val(isRequired);
    $("#itemLength").val(itemLength);
    $("#defaultValue").val(defaultValue);

    // // التحقق من الخيارات وإعادة بناء الجدول
    // if (!selectOptions || selectOptions.trim() === '') {
    //     console.error('selectOptions is empty.');
    //     return;
    // }

    var lang=$(".lang-input");
    var len=$(".lang-input").length;
    var array = itemName.split(","); // تحويل النص إلى مصفوفة باستخدام split()

    // alert(len);
    itemname2=$(".itemName22");
    for (let i = 0; i < len; i++) {
        console.log(i);
        // alert(array[i]);

        $(itemname2[i]).val(array[i]);
        // alert("val="+$(itemname2[i]).val());
        // alert("#faild"+idx+" [name='item"+$(lang[i]).val()+"[selectName][][]']");
        selectOptions2=$("#faild"+idx+" [name='item[selectName"+$(lang[i]).val()+"][][]']").val();
        // alert(selectOptions2);


        const selectOptionsArray = selectOptions2.split(','); // تحويل النص إلى قائمة

        console.log(selectOptionsArray);
        $("#itemTableBody"+$(lang[i]).val()).html(''); // إعادة تعيين الجدول
    
        
        selectOptionsArray.forEach(option => {
            const itemValue = option.trim(); // إزالة الفراغات الزائدة
            if (itemValue) {
                const tableBody = document.getElementById('itemTableBody'+$(lang[i]).val());
                const newRow = document.createElement('tr');
    
                // إنشاء خلية العنصر
                const itemCell = document.createElement('td');
                itemCell.textContent = itemValue;
                newRow.appendChild(itemCell);
    
                // إنشاء خلية الإجراءات
                const actionCell = document.createElement('td');
                const deleteButton = document.createElement('button');
                deleteButton.textContent = 'Delete'; // استبدل النص بالترجمة
                deleteButton.className = 'btn btn-danger btn-sm';
                deleteButton.onclick = function () {
                    newRow.remove();
                };
                actionCell.appendChild(deleteButton);
                newRow.appendChild(actionCell);
    
                // إضافة الصف إلى الجدول
                tableBody.appendChild(newRow);
            } else {
                console.warn('Empty option detected.');
            }
        });
    }
}




function editFaildItem(idx){
    if($("#itemType").val()=="" || $("#itemName").val()=="" || $("#itemName").val()==null || $("#itemName").val()==undefined){
        showToast("Error: Must Enter All Fields", "error"); // عرض رسالة الخطأ باستخدام Toast
        return;
    }
    else if($("#itemType").val()=="select" || $("#itemName").val()=="radios" || $("#itemName").val()=="checkbox"){
        if($("#itemTableBody").html()==""){
            showToast("Error: Must Add Option On Type Faild "+$("#itemType").val(), "error"); // عرض رسالة الخطأ باستخدام Toast
            return;
        }
    }

    // const itemName = document.getElementById("itemName").value;
    const itemType = document.getElementById("itemType").value;
    const itemOrder = document.getElementById("itemOrder").value;
    const isRequired = document.getElementById("isRequired").value;
    const itemLength = document.getElementById("itemLength").value;
    const defaultValue = document.getElementById("defaultValue").value;

    var selectOptions = [];
    const itemName = [];

    var value=``;


    var lang=$(".lang-input");
    var len=$(".lang-input").length;
    
    for (let i = 0; i < len; i++) {
        selectOptions = [];
        // const element = array[i];
        // alert($(lang[i]).val());

        if($("#itemTableBody"+$(lang[i]).val()+" tr").length==0){
            if($("#itemType").val()=="select" || $("#itemType").val()=="radios" || $("#itemType").val()=="checkbox"){

                if($("#itemTableBody"+$(lang[0]).val()+" tr").length==0){
                    showToast("Error: Must Enter All Select Fields", "error"); // عرض رسالة الخطأ باستخدام Toast
                    return;
                }
                else{
                    document.querySelectorAll("#itemTableBody"+$(lang[0]).val()+" tr").forEach((row) => {
                        var selectName = row.querySelector("td:first-child").textContent;
                        // alert(selectName);
                        selectOptions.push(selectName);
                        
                    });
                }
            }
        }
        document.querySelectorAll("#itemTableBody"+$(lang[i]).val()+" tr").forEach((row) => {
            var selectName = row.querySelector("td:first-child").textContent;
            // alert(selectName);
            

                selectOptions.push(selectName);
            
        });
        var itemname2=$(".itemName22");

        // alert($(itemname2[i]).val());
        if($(itemname2[i]).val()=="" || $(itemname2[i]).val() ==undefined || $(itemname2[i]).val()==null){
            itemName.push($(itemname2[0]).val());
        }
        else{
            itemName.push($(itemname2[i]).val());
        }
        value+=`<input type='hidden' name='item[selectName${$(lang[i]).val()}][][]' value="`+selectOptions+`" />`;
    }





    // document.querySelectorAll("#itemTableBody tr").forEach((row) => {
    //     const selectName = row.querySelector("td:first-child").textContent;
    //     // alert(selectName);
    //     selectOptions.push(selectName);
    // });

    // if(selectOptions.length>0){
    //     value=`<input type='hidden' name='item[selectName][][]' value="`+selectOptions+`" />`;
    // }

    
    const html = `
        <td>${itemName[0]}</td>
        <td>${itemType}</td>
        <td>
            <button onclick="openformedit('${idx}','${itemName}','${itemType}','${itemOrder}','${isRequired}','${itemLength}','${defaultValue}','${selectOptions}');" type="button" class="btn btn-primary" data-toggle="modal" data-target="#ServiceNowModal"><i class="tio-edit"></i></button>
            <button onclick='deleteFaildItem('${idx}')' type="button" class="btn btn-danger"><i class="tio-delete"></i></button>
        </td>
    
        <input type="hidden" name="item[Name][]" value="${itemName}" />
        <input type="hidden" name="item[Type][]" value="${itemType}" />
        <input type="hidden" name="item[Order][]" value="${itemOrder}" />
        <input type="hidden" name="item[isRequired][]" value="${isRequired}" />
        <input type="hidden" name="item[Length][]" value="${itemLength}" />
        <input type="hidden" name="item[defaultValue][]" value="${defaultValue}" />
    ${value}`;


    $("#faild"+idx).html(html);

    $('#ServiceNowModal').modal('hide');
    index++;
    document.getElementById("itemName").value="";
    $("#itemTableBody").html('');

}



function deleteFaildItem(idx){
    $("#faild"+idx).remove();
}



function addItemSelectButton2(lang) {
    var itemInput = document.getElementById('itemSelectInput'+lang);
    var itemValue = itemInput.value.trim();
    
    if (itemValue) {
        var tableBody = document.getElementById('itemTableBody'+lang);
        var newRow = document.createElement('tr');

        var itemCell = document.createElement('td');
        itemCell.textContent = itemValue;
        newRow.appendChild(itemCell);

        var actionCell = document.createElement('td');
        var deleteButton = document.createElement('button');
        deleteButton.textContent = 'Delete'; // استبدل 'Delete' بالترجمة إذا كنت تحتاج
        deleteButton.className = 'btn btn-danger btn-sm';
        deleteButton.onclick = function () {
            newRow.remove();
        };
        actionCell.appendChild(deleteButton);
        newRow.appendChild(actionCell);

        tableBody.appendChild(newRow);
        itemInput.value = '';
    } else {
        alert('Please enter an item'); // استبدل بالنص المترجم
    }
}



// دالة عرض رسالة Toast
function showToast(message, type) {
    const toast = document.createElement("div");
    toast.className = `toast ${type}`;
    toast.textContent = message;

    // إضافة CSS بسيط للـ Toast
    const style = `
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            z-index: 9999;
            font-size: 14px;
            opacity: 0;
            animation: fadeInOut 3s forwards;
        }
        .toast.error {
            background-color: #d9534f;
        }
        .toast.success {
            background-color: #5cb85c;
        }
        @keyframes fadeInOut {
            0% { opacity: 0; transform: translateY(10px); }
            10% { opacity: 1; transform: translateY(0); }
            90% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(10px); }
        }
    `;
    const styleSheet = document.createElement("style");
    styleSheet.type = "text/css";
    styleSheet.innerText = style;
    document.head.appendChild(styleSheet);

    document.body.appendChild(toast);

    // إزالة الـ Toast بعد 3 ثوانٍ
    setTimeout(() => {
        toast.remove();
    }, 3000);
}




