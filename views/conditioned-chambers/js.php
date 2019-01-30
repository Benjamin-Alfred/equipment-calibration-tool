<script>
jQuery( document ).ready(function( $ ) {
    $( function() {
        $( "#date_performed" ).datepicker({ dateFormat: 'yy-mm-dd' });
    } );

    $( "#status-pass" ).click(function() {
        url = "<?php echo get_site_url().'/conditioned-chambers/'; ?>";
        $.post( url, {ccc_id: $( "#ccc_id").val(), status: "PASSED", api_code: 6} )
            .done(function(data) {
                $( "#ccc_back" ).submit();
            });
    });

    $( "#status-fail" ).click(function() {
        url = "<?php echo get_site_url().'/conditioned-chambers/'; ?>";
        $.post( url, {ccc_id: $( "#ccc_id").val(), status: "FAILED", api_code: 6} )
            .done(function(data) {
                $( "#ccc_back" ).submit();
            });
    });

    $( "#add_client_button" ).click(function() {
        url = "<?php echo get_site_url().'/conditioned-chambers/'; ?>";
        $.post( 
            url, 
            {client_name: $( "#client_name").val(), api_code: 15} 
        ).done(function(data) {
                var newOptions = "", i, clients;
                clients = JSON.parse(data);
                for(i = 0; i < clients.length; i++){
                    newOptions += "<option value='" + clients[i].id + "'>" + clients[i].name + "</option>";
                }
                $("#client").html(newOptions);
            });
    });

    $( "#add_manufacturer_button" ).click(function() {
        url = "<?php echo get_site_url().'/conditioned-chambers/'; ?>";
        $.post( 
            url, 
            {manufacturer_name: $( "#manufacturer_name").val(), api_code: 12} 
        ).done(function(data) {
                var newOptions = "", i, manufacturers;
                manufacturers = JSON.parse(data);
                for(i = 0; i < manufacturers.length; i++){
                    newOptions += "<option value='" + manufacturers[i].id + "'>" + manufacturers[i].name + "</option>";
                }
                $("#manufacturer").html(newOptions);
                $("#ste_manufacturer").html(newOptions);
            });
    });

    $( "#add_equipment_button" ).click(function() {
        url = "<?php echo get_site_url().'/conditioned-chambers/'; ?>";
        $.post( 
            url, 
            {equipment_name: $( "#equipment_name").val(), api_code: 13} 
        ).done(function(data) {
                var newOptions = "", i, equipments;
                equipments = JSON.parse(data);
                for(i = 0; i < equipments.length; i++){
                    newOptions += "<option value='" + equipments[i].id + "'>" + equipments[i].name + "</option>";
                }
                $("#equipment").html(newOptions);
            });
    });

    $( "#add_ste_equipment_button" ).click(function() {
        url = "<?php echo get_site_url().'/conditioned-chambers/'; ?>";
        $.post( 
            url, 
            {s_t_equipment_name: $( "#s_t_equipment_name").val(), api_code: 14} 
        ).done(function(data) {
                var newOptions = "", i, equipments;
                equipments = JSON.parse(data);
                for(i = 0; i < equipments.length; i++){
                    newOptions += "<option value='" + equipments[i].id + "'>" + equipments[i].name + "</option>";
                }
                $("#ste_equipment").html(newOptions);
            });
    });

    $( "#add_client_contact_button" ).click(function() {
        url = "<?php echo get_site_url().'/conditioned-chambers/'; ?>";
        $.post( 
            url, 
            {
                client_id: $("#client_id").val(),
                contact_name: $( "#contact_name").val(), 
                contact_email: $( "#contact_email").val(), 
                contact_phone: $( "#contact_phone").val(), 
                api_code: 16
            } 
        ).done(function(data) {
                var newOptions = "", i, clients;
                clients = JSON.parse(data);
                for(i = 0; i < clients.length; i++){
                    newOptions += "<option value='" + clients[i].id + "'>" + clients[i].name + "</option>";
                }
                $("#client_contact_id").html(newOptions);
            });
    });


    $( "#conditioned-chambers-list" ).DataTable();

});
</script>
