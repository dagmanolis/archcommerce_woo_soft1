jQuery(function ($) {
    var timer1;
    var init_sync_process_started = false;
    $(document).ready(() => {
        get_active_sync_process();
    });

    var get_active_sync_process = function () {
        $.ajax({
            url: wpobj.ajaxurl,
            type: "POST",
            data: {
                action: "archcommerce_get_active_products_sync_process",
                security: wpobj.nonce,
            },
            success: function (response) {
                register_timer_if_not_registered();
                update_display(response);
            },
            error: function (xhr) {
                handle_get_process_error(xhr);
            }
        });
    }
    var init_sync_process = function () {
        disable_init_button();
        if (!confirm(wpobj.areyousure_message)) {
            enable_init_button();
            return;
        }

        $.ajax({
            url: wpobj.ajaxurl,
            type: "POST",
            data: {
                action: "archcommerce_init_products_sync_process",
                security: wpobj.nonce,
            },
            success: function (response) {
                if (response.success) {
                    init_sync_process_started = true;
                } else {
                    handle_init_process_error(response);
                }
            },
            error: function (xhr) {
                handle_init_process_error(xhr);
            }
        });
    }
    var cancel_sync_process = function () {
        if (!confirm(wpobj.areyousure_cancel_message))
            return;

        init_sync_process_started = false;
        $.ajax({
            url: wpobj.ajaxurl,
            type: "POST",
            data: {
                action: "archcommerce_cancel_products_sync_process",
                security: wpobj.nonce,
            }
        });
    }
    var register_timer_if_not_registered = function () {
        if (timer1 === undefined)
            timer1 = setInterval(get_active_sync_process, 5000);
    }

    var update_display = function (response) {
        if (response.success) {
            var data = response.data;
            if (data === false) {
                disable_init_button();
                disable_cancel_button();
                show_notfound();
            }
            if (typeof (data.status) !== undefined) {
                switch (data.status) {
                    case "":
                        if (!init_sync_process_started)
                            enable_init_button();
                        disable_cancel_button();
                        show_notfound();
                        break;
                    case "finished":
                        if (!init_sync_process_started)
                            enable_init_button();
                        disable_cancel_button();
                        show_finished();
                        fill_asp_finished_fields(data);
                        init_sync_process_started = false;
                        break;
                    case "canceled":
                        if (!init_sync_process_started)
                            enable_init_button();
                        disable_cancel_button();
                        show_canceled();
                        init_sync_process_started = false;
                        break;
                    case "failed":
                        if (!init_sync_process_started)
                            enable_init_button();
                        disable_cancel_button();
                        show_failed();
                        init_sync_process_started = false
                        break;
                    case "init":
                        disable_init_button();
                        disable_cancel_button();
                        show_init();
                        fill_asp_init_fields(data);
                        break;
                    case "processing":
                    case "idle":
                        enable_cancel_button();
                        disable_init_button();
                        show_running();
                        fill_asp_running_fields(data);
                        break;
                }
            } else {
                handle_get_process_error(response);
                disable_init_button();
                disable_cancel_button();
            }
        } else {
            handle_get_process_error(response);
            disable_init_button();
            disable_cancel_button();
        }
    }

    var handle_init_process_error = function (data) {
        show_init_process_error();
        console.log(data);
        setTimeout(hide_init_process_error, 5000);
    }
    var handle_get_process_error = function (data) {
        show_get_process_error();
        console.log(data);
    }
    var show_get_process_error = function () {
        $("#asp_notfound").hide();
        $("#asp_running").hide();
        $("#asp_finished").hide();
        $("#loading").hide();
        $("#asp_error").show();
        $("#asp_init").hide();
    }
    var show_init_process_error = function () {
        $("#init_sync_porcess_error").show();
    }
    var hide_init_process_error = function () {
        $("#init_sync_porcess_error").hide();
    }
    var show_finished = function () {
        hide_all();
        $("#asp_finished").show();
    }
    var show_running = function () {
        hide_all();
        $("#asp_running").show();
    }
    var show_init = function () {
        hide_all();
        $("#asp_init").show();
    }
    var show_notfound = function () {
        hide_all();
        $("#asp_notfound").show();
    }
    var show_canceled = function () {
        hide_all();
        $("#asp_canceled").show();
    }
    var show_failed = function () {
        hide_all();
        $("#asp_failed").show();
    }

    var hide_all = function () {
        $("#asp_notfound").hide();
        $("#asp_running").hide();
        $("#asp_finished").hide();
        $("#asp_canceled").hide();
        $("#loading").hide();
        $("#asp_error").hide();
        $("#asp_init").hide();
        $("#asp_failed").hide();
    }
    var fill_asp_init_fields = function (data) {
        var date_created = new Date(data.created_at.date);
        $(".asp_process_id").html(data.process_id);
        $(".asp_started_at").html(format_date(date_created));
        $(".asp_batch_size").html(data.batch_size);
    }
    var fill_asp_running_fields = function (data) {
        var date_created = new Date(data.created_at.date);
        $(".asp_process_id").html(data.process_id);
        $(".asp_started_at").html(format_date(date_created));
        $(".asp_status").html(data.status);
        $(".asp_total_products").html(data.total_products);
        $(".asp_products_updated").html(data.products_updated);
        $(".asp_batch_size").html(data.batch_size);
        if (data.total_batches > 0) {
            var percentage = Math.round((data.current_batch * 100) / data.total_batches);
            if (percentage > 100) percentage = 100;
            $(".asp_percentage").html(percentage + "%");
        }
        else {
            $(".asp_percentage").html("0%");
        }
    }
    var fill_asp_finished_fields = function (data) {
        var date_created = new Date(data.created_at.date);
        var date_finished = new Date(data.finished_at.date);
        var duration = msToTime(date_finished - date_created);
        $(".asp_process_id").html(data.process_id);
        $(".asp_started_at").html(data.created_at);
        $(".asp_status").html(data.status);
        $(".asp_total_products").html(data.total_products);
        $(".asp_products_updated").html(data.products_updated);
        $(".asp_started_at").html(format_date(date_created));
        $(".asp_finished_at").html(format_date(date_finished));
        $(".asp_duration").html(duration);
    }

    var disable_init_button = function () {
        $("#init_sync_process").prop('disabled', true);
        $("#init_sync_process").off();
    }
    var enable_init_button = function () {
        $("#init_sync_process").prop('disabled', false);
        $("#init_sync_process").off().on('click', init_sync_process);
    }

    var enable_cancel_button = function () {
        $("#cancel_sync_process").prop('disabled', false);
        $("#cancel_sync_process").off().on('click', cancel_sync_process);
    }
    var disable_cancel_button = function () {
        $("#cancel_sync_process").prop('disabled', true);
        $("#cancel_sync_process").off();
    }

    var msToTime = function (duration) {
        var milliseconds = parseInt((duration % 1000) / 100),
            seconds = Math.floor((duration / 1000) % 60),
            minutes = Math.floor((duration / (1000 * 60)) % 60),
            hours = Math.floor((duration / (1000 * 60 * 60)) % 24);

        hours = (hours < 10) ? "0" + hours : hours;
        minutes = (minutes < 10) ? "0" + minutes : minutes;
        seconds = (seconds < 10) ? "0" + seconds : seconds;

        return hours + ":" + minutes + ":" + seconds + "." + milliseconds;
    }

    var format_date = function (date) {

        var result = date.getFullYear() + "-" +
            format_digit(date.getMonth() + 1) + "-" +
            format_digit(date.getDate()) + " " +
            format_digit(date.getHours()) + ":" +
            format_digit(date.getMinutes()) + ":" +
            format_digit(date.getSeconds());
        return result;
    }

    var format_digit = function (digit) {
        var result = digit.toString();
        if (result.toString().length == 1)
            result = "0" + digit.toString();
        return result;
    }
});