
$(function() {
    
    // Resize the elements of the page.
    
    $(window).on("resize", function() {
        var width = $(window).width();
        var height = $(window).height();
        var sidebar = $("#sidebar").width() + 24;
        $("#tabs").width(width - sidebar - 1);
        $("#sidebar").height(height - 64);
        $("#editors, #editors div.instance").width(width - sidebar).height(height - 40);
    });
    
    $(window).triggerHandler("resize");
    
    // Make the tabs sortable using jQuery UI.
    
    $("#tabs").sortable({ items: "div.tab:not(.console)" });
    
    // Function for adding a new tab.
    
    function add_tab(data) {
        if ($("#tab_" + data.editorid).size()) {
            $("#tab_" + data.editorid + " a.tab_link").trigger("click");
            return;
        }
        $("#tabs div.tab").removeClass("selected");
        $("#editors div.instance").css("display", "none");
        $("#encoding").val(data.encoding);
        document.title = "QADE - " + data.basename;
        var tab = $('<div class="tab selected"></div>');
        tab.attr("id", "tab_" + data.editorid);
        tab.data("filename", data.filename);
        tab.append($('<span class="unsaved"></span>'));
        tab.append($('<a class="tab_link" href="javascript:void(0)"></a>').text(data.basename));
        tab.append($('<a class="tab_close" href="javascript:void(0)"><i class="fa fa-times"></i></a>'));
        tab.appendTo($("#tabs"));
        var item = $('<div class="instance"></div>');
        item.attr("id", "instance_" + data.editorid);
        item.data("filename", data.filename);
        item.data("ext", data.extension);
        item.data("encoding", data.encoding);
        item.text(data.content);
        item.appendTo($("#editors"));
        $(window).triggerHandler("resize");
        initialize_editor(item);
    }
    
    // Function for initializing editor instances.
    
    function initialize_editor(jQueryObject) {
        
        var editorid = jQueryObject.attr("id").substr(9);
        var filename = jQueryObject.data("filename");
        var ext = jQueryObject.data("ext");
        var editor = ace.edit("instance_" + editorid);
        
        editor.setTheme("ace/theme/chrome");
        editor.setBehavioursEnabled(false);
        editor.commands.bindKeys({
            "ctrl-l" : null, "ctrl-s" : null, "ctrl-t" : null,
            "cmd-l" : null, "cmd-s" : null, "cmd-t" : null,
        });
        
        editor.getSession().setTabSize(4);
        editor.getSession().setUseSoftTabs(true);
        editor.getSession().setMode("ace/mode/" + ext);
        
        editor.getSession().on('change', function(e) {
            $("#tab_" + editorid).find("span.unsaved").text("*");
        });
    }
    
    // Function for saving the contents of a tab.
    
    function save(editorid) {
        if (!$("#instance_" + editorid).size()) return;
        var filename = $("#instance_" + editorid).data("filename");
        var encoding = $("#instance_" + editorid).data("encoding");
        var content = ace.edit("instance_" + editorid).getValue();
        $.ajax({
            url: "post_edit.php",
            method: "post",
            data: { file: filename, content: content, encoding: encoding, token: $("body").data("token") },
            dataType: "text",
            processData: true,
            cache: false,
            success: function(data, textStatus, jqXHR) {
                if (data === "OK") {
                    $("#tab_" + editorid + " span.unsaved").text("");
                } else {
                    alert(data);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert(textStatus);
            }
        });
    }
    
    // Attach events to the file browser.
    
    $("#sidebar").on("click", "div.dirtree a.dir", function(e) {
        e.preventDefault();
        var parent = $(this).parents("div.dirtree");
        var path = parent.data("path");
        var child_margin_left = parseInt(parent.css("margin-left").replace("px", ""), 10) + 17;
        if ($(this).find("i.fa").hasClass("fa-folder")) {
            $(this).find("i.fa").removeClass("fa-folder").addClass("fa-folder-open");
            $.ajax({
                url: "open_dir.php",
                method: "get",
                data: { dir: path, token: $("body").data("token") },
                dataType: "json",
                processData: true,
                cache: false,
                success: function(data, textStatus, jqXHR) {
                    $.each(data.files, function(index, value) {
                       var item = $('<div class="dirtree"></div>').css("margin-left", child_margin_left + "px");
                       item.data("path", path + "/" + value);
                       var link = $('<a class="file" href="javascript:void(0)"></a>').text(" " + value);
                       link.prepend('<i class="fa fa-file-text-o"></i>');
                       link.appendTo(item);
                       item.insertAfter(parent);
                    });
                    $.each(data.dirs, function(index, value) {
                       var item = $('<div class="dirtree"></div>').css("margin-left", child_margin_left + "px");
                       item.data("path", path + "/" + value);
                       var link = $('<a class="dir" href="javascript:void(0)"></a>').text(" " + value);
                       link.prepend('<i class="fa fa-folder"></i>');
                       link.appendTo(item);
                       item.insertAfter(parent);
                    });
                }
            });
        } else {
            $(this).find("i.fa").removeClass("fa-folder-open").addClass("fa-folder");
            $("#sidebar div.dirtree").each(function() {
                if ($(this).data("path").indexOf(path + "/") === 0) {
                    $(this).remove();
                }
            });
        }
    });
    
    $("#sidebar").on("click", "div.dirtree a.file", function(e) {
        e.preventDefault();
        var parent = $(this).parents("div.dirtree");
        var path = parent.data("path");
        $.ajax({
            url: "open_file.php",
            method: "get",
            data: { file: path, token: $("body").data("token") },
            dataType: "json",
            processData: true,
            cache: false,
            success: function(data, textStatus, jqXHR) {
                if (data.error) {
                    alert(data.error);
                } else {
                    add_tab(data);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert(textStatus);
            }
        });
    });
    
    // Attach events to tabs.
    
    $("#tabs").on("click", "div.tab a.tab_link", function(e) {
        e.preventDefault();
        document.title = "QADE - " + $(this).text();
        var parent = $(this).parents("div.tab");
        var editorid = parent.attr("id").substr(4);
        var encoding = $("#instance_" + editorid).data("encoding");
        $("#editors div.instance").css("display", "none");
        $("#instance_" + editorid).css("display", "block");
        $("#tabs div.tab").removeClass("selected");
        $("#encoding").val(encoding);
        parent.addClass("selected");
        if (editorid === "console") {
            $("#console_cmd").focus();
        } else {
            ace.edit("instance_" + editorid).resize();
            ace.edit("instance_" + editorid).focus();
        }
    });
    
    $("#tabs").on("click", "div.tab a.tab_close", function(e) {
        e.preventDefault();
        if ($("#tabs div.tab").size() <= 1) {
            alert("Cannot close last tab.");
            return;
        }
        var parent = $(this).parents("div.tab");
        var editorid = parent.attr("id").substr(4);
        $("#instance_" + editorid).remove();
        var is_last = $("#tabs div.tab:last-of-type").attr("id") === parent.attr("id");
        var other_id = "";
        if (parent.hasClass("selected")) {
            if (is_last) {
                other_id = parent.prev().attr("id").substr(4);
            } else {
                other_id = parent.next().attr("id").substr(4);
            }
            var encoding = $("#instance_" + other_id).data("encoding");
            $("#tab_" + other_id).find("a.tab_link").trigger("click");
            parent.remove();
        } else {
            parent.remove();
        }
    });
    
    // Attach events to the New file button.
    
    $("#new").click(function(e) {
        $("#new_file_dialog").dialog({
            height : 208,
            width : 480,
            modal : true,
            buttons : {
                Create : function() {
                    $.ajax({
                        url: "post_new.php",
                        method: "post",
                        data: {
                            dir: $("#new_file_dir").val(),
                            filename: $("#new_file_filename").val(),
                            token: $("body").data("token")
                        },
                        dataType: "json",
                        processData: true,
                        cache: false,
                        success: function(data, textStatus, jqXHR) {
                            if (data.error) {
                                alert(data.error);
                            } else {
                                $("#new_file_dialog").dialog("close");
                                add_tab(data);
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            alert(textStatus);
                        }
                    });
                },
                Cancel : function() {
                    $(this).dialog("close");
                }
            }
        });
        $("#new_file_filename").focus();
    });
    
    // Attach events to the Save button.
    
    $("#save").click(function(e) {
        e.preventDefault();
        var editorid = $("#tabs div.selected").first().attr("id").substr(4);
        if ($("#tab_" + editorid + " span.unsaved").first().text() !== "*") return;
        save(editorid);
    });
    
    // Attach events to the encoding changer.
    
    $("#encoding").change(function(e) {
        var editorid = $("#tabs div.selected").first().attr("id").substr(4);
        var prev_encoding = $("#instance_" + editorid).data("encoding");
        var yes = confirm("Changing the encoding might break your program. Continue?");
        if (!yes) {
            $("#encoding").val(prev_encoding);
            return;
        }
        var prev_encoding = $("#instance_" + editorid).data("encoding", $("#encoding").val());
        $("#tab_" + editorid + " span.unsaved").text("*");
    });
    
    // Attach events to the Ctrl+S keyboard shortcut.
    
    $(document).keypress(function(e) {
        if (e.ctrlKey && String.fromCharCode(e.which).toLowerCase() === 's') {
            $("#save").triggerHandler("click");
            e.preventDefault();
        }
    });
    
    // Attach keyboard shortcuts to the console.
    
    var console_history = [];
    var console_history_position = 0;
    
    $("#console_cmd").keydown(function(e) {
        if (e.which == 13) {  // Enter
            var dir = $("#console_output").data("dir");
            var cmd = $("#console_cmd").val().trim();
            if (cmd !== "") {
                console_history.push(cmd);
                console_history_position = console_history.length;
            }
            var prompt = $("#console_output").data("username") + "@" + $("#console_output").data("hostname") + ":" + dir + "$";
            $("#console_output div.placeholder").remove();
            $("#console_output").append($('<div class="item" style="color:#00a;font-weight:bold;margin:8px 0"></div>').text(prompt + " " + cmd));
            $("#console_output").append($('<div class="item temporary"></div>').text("executing..."));
            $.ajax({
                url: "post_cmd.php",
                method: "post",
                data: { dir: dir, cmd: cmd, token: $("body").data("token") },
                dataType: "text",
                processData: true,
                cache: false,
                success: function(data, textStatus, jqXHR) {
                    if (data.length >= 3 && data.substr(0, 3) === "OK\n") {
                        var datax = data.substr(3);
                        var newdir = datax.substr(0, datax.indexOf("\n"));
                        var output = datax.substr(datax.indexOf("\n") + 1);
                        $("#console_output").data("dir", newdir);
                        $("#console_output").find("div.temporary").removeClass("temporary").text(output);
                        $("#console_cmd").val("");
                        $("#instance_console").scrollTop($("#instance_console").get(0).scrollHeight - $("#instance_console").height());
                    } else {
                        alert(data);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert(textStatus);
                }
            });
        }
        if (e.which == 38) {  // Up
            if (console_history.length < 1 || console_history_position < 1) return;
            console_history_position--;
            $("#console_cmd").val(console_history[console_history_position]);
        }
        if (e.which == 40) {  // Down
            if (console_history_position >= console_history.length) return;
            console_history_position++;
            if (console_history_position >= console_history.length) {
                $("#console_cmd").val("");
            } else {
                $("#console_cmd").val(console_history[console_history_position]);
            }
        }
    });
    
    // On page load, load previously open directories and files.
    
    $(open_dirs).each(function(index, value) {
        $("#sidebar div.dirtree").each(function() {
            if ($(this).data("path") === value) {
                $.ajaxSetup({ async: false });
                $(this).find("a.dir").trigger("click");
                $.ajaxSetup({ async: true });
            }
        });
    });
    
    $(open_files).each(function(index, value) {
        if (!value.error) add_tab(value);
    });
    
    if (selected_tab === "") {
        $("#tab_console a.tab_link").trigger("click");
    } else {
        $("#tabs div.tab").not(".console").each(function() {
            if ($(this).data("filename") === selected_tab) {
                $(this).find("a.tab_link").trigger("click");
            }
        });
    }
    
    $("#tabs tab.selected").each(function() {
        if ($(this).hasClass("console")) {
            $("#console_cmd").focus();
        } else {
            var editorid = $(this).attr("id").substr(4);
            ace.edit("instance_" + editorid).focus();
        }
    });
    
    // On unload, save currently open directories and tabs.

    $(window).on("beforeunload", function() {
        var open_dirs = [];
        var open_files = [];
        var selected = "";
        $("#sidebar div.dirtree").each(function() {
            if ($(this).find("i.fa-folder-open").size()) {
                open_dirs.push($(this).data("path"));
            }
        });
        $("#tabs div.tab").not(".console").each(function() {
            open_files.push($(this).data("filename"));
            if ($(this).hasClass("selected")) {
                selected = $(this).data("filename");
            }
        });
        $.ajax({
            url: "post_state.php",
            method: "post",
            data: { open_dirs: open_dirs, open_files: open_files, selected: selected, token: $("body").data("token") },
            dataType: "text",
            processData: true,
            cache: false,
            async: false
        });
        var unsaved_exists = false;
        $("#tabs span.unsaved").each(function() {
           if ($(this).text() !== "") unsaved_exists = true; 
        });
        if (unsaved_exists) {
            return "There are unsaved changes. Close window?";
        }
    });
    
});
