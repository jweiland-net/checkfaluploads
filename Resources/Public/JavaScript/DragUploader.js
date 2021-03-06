/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/* SF: All relative Paths like ./Enum/Severity has to be moved back to EXT:backend like TYPO3/CMS/Backend/Enum/Severity */
define(["require", "exports", "TYPO3/CMS/Backend/Enum/Severity", "jquery", "moment", "nprogress", "TYPO3/CMS/Backend/Modal", "TYPO3/CMS/Backend/Notification"], function (e, t, i, r, a, s, o, n) {
    "use strict";
    var d, l;
    Object.defineProperty(t, "__esModule", {value: !0}), (l = d || (d = {})).OVERRIDE = "replace", l.RENAME = "rename", l.SKIP = "cancel", l.USE_EXISTING = "useExisting";
    var p = function () {
        function e(e) {
            var t = this;
            this.askForOverride = [], this.percentagePerFile = 1, this.hideDropzone = function (e) {
                /* SF: Add t.$checkForFalUploads.hide() to hide checkbox for FAL uploads */
                t.$checkForFalUploads.hide(), e.stopPropagation(), e.preventDefault(), t.$dropzone.hide()
            }, this.dragFileIntoDocument = function (e) {
                return e.stopPropagation(), e.preventDefault(), r(e.currentTarget).addClass("drop-in-progress"), t.showDropzone(), !1
            }, this.dragAborted = function (e) {
                return e.stopPropagation(), e.preventDefault(), r(e.currentTarget).removeClass("drop-in-progress"), !1
            }, this.ignoreDrop = function (e) {
                return e.stopPropagation(), e.preventDefault(), t.dragAborted(e), !1
            }, this.handleDrop = function (e) {
                t.ignoreDrop(e), t.processFiles(e.originalEvent.dataTransfer.files), t.$dropzone.removeClass("drop-status-ok")
            }, this.fileInDropzone = function () {
                t.$dropzone.addClass("drop-status-ok")
            }, this.fileOutOfDropzone = function () {
                t.$dropzone.removeClass("drop-status-ok")
            }, this.$body = r("body"), this.$element = r(e);
            var i = void 0 !== this.$element.data("dropzoneTrigger");
            this.$trigger = r(this.$element.data("dropzoneTrigger")), this.$dropzone = r("<div />").addClass("dropzone").hide(), this.irreObjectUid = this.$element.data("fileIrreObject");
            var a = this.$element.data("dropzoneTarget");
            /* SF: Add HTML for checkbox. r = jQuery
             * this.$checkboxForRights = r('<label for="checkFalUploads"><input type="checkbox" id="checkFalUploads" value="1" />' + TYPO3.lang['dragUploader.iHaveTheRights'] + '</label>');
             * this.$checkForFalUploads = r('<div class="checkbox" />').append(this.$checkboxForRights).hide();
             * this.$dropzone.before(this.$checkForFalUploads);
             */
            this.irreObjectUid && 0 !== this.$element.nextAll(a).length ? (this.dropZoneInsertBefore = !0, this.$dropzone.insertBefore(a)) : (this.dropZoneInsertBefore = !1, this.$dropzone.insertAfter(a)), this.$checkboxForRights = r(
                '<div class="alert alert-warning">' +
                '  <div class="media">' +
                '    <div class="media-left">' +
                '      <span class="fa-stack fa-lg">' +
                '        <i class="fa fa-circle fa-stack-2x"></i>' +
                '        <i class="fa fa-exclamation fa-stack-1x"></i>' +
                '      </span>' +
                '    </div>' +
                '    <div class="media-body">' +
                '      <h4 class="alert-title">' + TYPO3.lang['dragUploader.fileRights.title'].replace('%s', TYPO3.settings.checkfaluploads.owner) + '</h4>' +
                '      <p class="alert-message">' +
                '        <div class="form-check">' +
                '          <input type="checkbox" class="form-check-input" id="checkFalUploads" value="1" />' +
                '          <label for="checkFalUploads">' + TYPO3.lang['dragUploader.fileRights.confirmation'] + '</label>' +
                '        </div>' +
                '      </p>' +
                '    </div>' +
                '  </div>' +
                '</div>'
            ), this.$checkForFalUploads = r('<div class="typo3-messages" />').append(this.$checkboxForRights).hide(), this.$dropzone.before(this.$checkForFalUploads), this.$dropzoneMask = r("<div />").addClass("dropzone-mask").appendTo(this.$dropzone), this.fileInput = document.createElement("input"), this.fileInput.setAttribute("type", "file"), this.fileInput.setAttribute("multiple", "multiple"), this.fileInput.setAttribute("name", "files[]"), this.fileInput.classList.add("upload-file-picker"), this.$body.append(this.fileInput), this.$fileList = r(this.$element.data("progress-container")), this.fileListColumnCount = r("thead tr:first th", this.$fileList).length, this.filesExtensionsAllowed = this.$element.data("file-allowed"), this.fileDenyPattern = this.$element.data("file-deny-pattern") ? new RegExp(this.$element.data("file-deny-pattern"), "i") : null, this.maxFileSize = parseInt(this.$element.data("max-file-size"), 10), this.target = this.$element.data("target-folder"), this.browserCapabilities = {
                fileReader: "undefined" != typeof FileReader,
                DnD: "draggable" in document.createElement("span"),
                FormData: !!window.FormData,
                Progress: "upload" in new XMLHttpRequest
            }, this.browserCapabilities.DnD ? (this.$body.on("dragover", this.dragFileIntoDocument), this.$body.on("dragend", this.dragAborted), this.$body.on("drop", this.ignoreDrop), this.$dropzone.on("dragenter", this.fileInDropzone), this.$dropzoneMask.on("dragenter", this.fileInDropzone), this.$dropzoneMask.on("dragleave", this.fileOutOfDropzone), this.$dropzoneMask.on("drop", function (e) {
                return t.handleDrop(e)
            }), this.$dropzone.prepend('<div class="dropzone-hint"><div class="dropzone-hint-media"><div class="dropzone-hint-icon"></div></div><div class="dropzone-hint-body"><h3 class="dropzone-hint-title">' + TYPO3.lang["file_upload.dropzonehint.title"] + '</h3><p class="dropzone-hint-message">' + TYPO3.lang["file_upload.dropzonehint.message"] + "</p></div></div>").click(function () {
                t.fileInput.click()
            }), r("<span />").addClass("dropzone-close").click(this.hideDropzone).appendTo(this.$dropzone), 0 === this.$fileList.length && (this.$fileList = r("<table />").attr("id", "typo3-filelist").addClass("table table-striped table-hover upload-queue").html("<tbody></tbody>").hide(), this.dropZoneInsertBefore ? this.$fileList.insertAfter(this.$dropzone) : this.$fileList.insertBefore(this.$dropzone), this.fileListColumnCount = 7), this.fileInput.addEventListener("change", function () {
                t.processFiles(Array.apply(null, t.fileInput.files))
            }), this.bindUploadButton(!0 === i ? this.$trigger : this.$element)) : console.warn("Browser has no Drag and drop capabilities; cannot initialize DragUploader")
        }

        return e.prototype.showDropzone = function () {
            /* SF: Add this.$checkForFalUploads.show() to add checkbox for FAL uploads */
            this.$dropzone.show(), this.$checkForFalUploads.show()
        }, e.prototype.processFiles = function (e) {
            var t = this;
            this.queueLength = e.length, this.$fileList.is(":visible") || this.$fileList.show(), s.start(), this.percentagePerFile = 1 / e.length;
            var i = [];
            r.each(e, function (e, a) {
                i[parseInt(e, 10)] = r.ajax({
                    url: TYPO3.settings.ajaxUrls.file_exists,
                    data: {fileName: a.name, fileTarget: t.target},
                    cache: !1,
                    success: function (e) {
                        if (void 0 !== e.uid) t.askForOverride.push({
                            original: e,
                            uploaded: a,
                            action: t.irreObjectUid ? d.USE_EXISTING : d.SKIP
                        }), s.inc(t.percentagePerFile); else new h(t, a, d.SKIP)
                    }
                })
            }), r.when.apply(r, i).done(function () {
                t.drawOverrideModal(), s.done()
            }), this.fileInput.value = ""
        }, e.prototype.bindUploadButton = function (e) {
            var t = this;
            e.click(function (e) {
                /* SF: Remove this.$fileInput.click() to prevent loading upload screen */
                e.preventDefault(), t.showDropzone()
            })
        }, e.prototype.decrementQueueLength = function () {
            this.queueLength > 0 && (this.queueLength--, 0 === this.queueLength && r.ajax({
                url: TYPO3.settings.ajaxUrls.flashmessages_render,
                cache: !1,
                success: function (e) {
                    r.each(e, function (e, t) {
                        n.showMessage(t.title, t.message, t.severity)
                    })
                }
            }))
        }, e.prototype.drawOverrideModal = function () {
            var e = this, t = Object.keys(this.askForOverride).length;
            if (0 !== t) {
                for (var s = r("<div/>").append(r("<p/>").text(TYPO3.lang["file_upload.existingfiles.description"]), r("<table/>", {class: "table"}).append(r("<thead/>").append(r("<tr />").append(r("<th/>"), r("<th/>").text(TYPO3.lang["file_upload.header.originalFile"]), r("<th/>").text(TYPO3.lang["file_upload.header.uploadedFile"]), r("<th/>").text(TYPO3.lang["file_upload.header.action"]))))), n = 0; n < t; ++n) {
                    var l = r("<tr />").append(r("<td />").append("" !== this.askForOverride[n].original.thumbUrl ? r("<img />", {
                        src: this.askForOverride[n].original.thumbUrl,
                        height: 40
                    }) : r(this.askForOverride[n].original.icon)), r("<td />").html(this.askForOverride[n].original.name + " (" + u.fileSizeAsString(this.askForOverride[n].original.size) + ")<br>" + a.unix(this.askForOverride[n].original.mtime).format("YYYY-MM-DD HH:mm")), r("<td />").html(this.askForOverride[n].uploaded.name + " (" + u.fileSizeAsString(this.askForOverride[n].uploaded.size) + ")<br>" + a(this.askForOverride[n].uploaded.lastModified ? this.askForOverride[n].uploaded.lastModified : this.askForOverride[n].uploaded.lastModifiedDate).format("YYYY-MM-DD HH:mm")), r("<td />").append(r("<select />", {
                        class: "form-control t3js-actions",
                        "data-override": n
                    }).append(this.irreObjectUid ? r("<option/>").val(d.USE_EXISTING).text(TYPO3.lang["file_upload.actions.use_existing"]) : "", r("<option />").val(d.SKIP).text(TYPO3.lang["file_upload.actions.skip"]), r("<option />").val(d.RENAME).text(TYPO3.lang["file_upload.actions.rename"]), r("<option />").val(d.OVERRIDE).text(TYPO3.lang["file_upload.actions.override"]))));
                    s.find("table").append("<tbody />").append(l)
                }
                var p = o.confirm(TYPO3.lang["file_upload.existingfiles.title"], s, i.SeverityEnum.warning, [{
                    text: r(this).data("button-close-text") || TYPO3.lang["file_upload.button.cancel"] || "Cancel",
                    active: !0,
                    btnClass: "btn-default",
                    name: "cancel"
                }, {
                    text: r(this).data("button-ok-text") || TYPO3.lang["file_upload.button.continue"] || "Continue with selected actions",
                    btnClass: "btn-warning",
                    name: "continue"
                }], ["modal-inner-scroll"]);
                p.find(".modal-dialog").addClass("modal-lg"), p.find(".modal-footer").prepend(r("<span/>").addClass("form-inline").append(r("<label/>").text(TYPO3.lang["file_upload.actions.all.label"]), r("<select/>", {class: "form-control t3js-actions-all"}).append(r("<option/>").val("").text(TYPO3.lang["file_upload.actions.all.empty"]), this.irreObjectUid ? r("<option/>").val(d.USE_EXISTING).text(TYPO3.lang["file_upload.actions.all.use_existing"]) : "", r("<option/>").val(d.SKIP).text(TYPO3.lang["file_upload.actions.all.skip"]), r("<option/>").val(d.RENAME).text(TYPO3.lang["file_upload.actions.all.rename"]), r("<option/>").val(d.OVERRIDE).text(TYPO3.lang["file_upload.actions.all.override"]))));
                var g = this;
                p.on("change", ".t3js-actions-all", function () {
                    var e = r(this).val();
                    "" !== e ? p.find(".t3js-actions").each(function (t, i) {
                        var a = r(i), s = parseInt(a.data("override"), 10);
                        a.val(e).prop("disabled", "disabled"), g.askForOverride[s].action = a.val()
                    }) : p.find(".t3js-actions").removeProp("disabled")
                }).on("change", ".t3js-actions", function () {
                    var e = r(this), t = parseInt(e.data("override"), 10);
                    g.askForOverride[t].action = e.val()
                }).on("button.clicked", function (e) {
                    "cancel" === e.target.name ? (g.askForOverride = [], o.dismiss()) : "continue" === e.target.name && (r.each(g.askForOverride, function (e, t) {
                        if (t.action === d.USE_EXISTING) u.addFileToIrre(g.irreObjectUid, t.original); else if (t.action !== d.SKIP) new h(g, t.uploaded, t.action)
                    }), g.askForOverride = [], o.dismiss())
                }).on("hidden.bs.modal", function () {
                    e.askForOverride = []
                })
            }
        }, e
    }(), h = function () {
        function e(e, t, i) {
            var a = this;
            if (this.dragUploader = e, this.file = t, this.override = i, this.$row = r("<tr />").addClass("upload-queue-item uploading"), this.$iconCol = r("<td />").addClass("col-icon").appendTo(this.$row), this.$fileName = r("<td />").text(t.name).appendTo(this.$row), this.$progress = r("<td />").attr("colspan", this.dragUploader.fileListColumnCount - 2).appendTo(this.$row), this.$progressContainer = r("<div />").addClass("upload-queue-progress").appendTo(this.$progress), this.$progressBar = r("<div />").addClass("upload-queue-progress-bar").appendTo(this.$progressContainer), this.$progressPercentage = r("<span />").addClass("upload-queue-progress-percentage").appendTo(this.$progressContainer), this.$progressMessage = r("<span />").addClass("upload-queue-progress-message").appendTo(this.$progressContainer), 0 === r("tbody tr.upload-queue-item", this.dragUploader.$fileList).length ? (this.$row.prependTo(r("tbody", this.dragUploader.$fileList)), this.$row.addClass("last")) : this.$row.insertBefore(r("tbody tr.upload-queue-item:first", this.dragUploader.$fileList)), this.$iconCol.html('<span class="t3-icon t3-icon-mimetypes t3-icon-other-other">&nbsp;</span>'), this.dragUploader.maxFileSize > 0 && this.file.size > this.dragUploader.maxFileSize) this.updateMessage(TYPO3.lang["file_upload.maxFileSizeExceeded"].replace(/\{0\}/g, this.file.name).replace(/\{1\}/g, u.fileSizeAsString(this.dragUploader.maxFileSize))), this.$row.addClass("error"); else if (this.dragUploader.fileDenyPattern && this.file.name.match(this.dragUploader.fileDenyPattern)) this.updateMessage(TYPO3.lang["file_upload.fileNotAllowed"].replace(/\{0\}/g, this.file.name)), this.$row.addClass("error"); else if (this.checkAllowedExtensions()) {
                this.updateMessage("- " + u.fileSizeAsString(this.file.size));
                var s = new FormData;
                s.append("data[upload][1][target]", this.dragUploader.target), s.append("data[upload][1][data]", "1"), s.append("overwriteExistingFiles", this.override), s.append("redirect", ""), s.append("upload_1", this.file);
                /* SF: Add value of checkbox to POST */
                if (r('#checkFalUploads').is(':checked')) {
                    s.append('userHasRights', '1');
                } else {
                    s.append('userHasRights', '0');
                }
                var o = r.extend(!0, {}, r.ajaxSettings, {
                    url: TYPO3.settings.ajaxUrls.file_process,
                    contentType: !1,
                    processData: !1,
                    data: s,
                    cache: !1,
                    type: "POST",
                    success: function (e) {
                        return a.uploadSuccess(e)
                    },
                    error: function (e) {
                        return a.uploadError(e)
                    }
                });
                o.xhr = function () {
                    var e = r.ajaxSettings.xhr();
                    return e.upload.addEventListener("progress", function (e) {
                        return a.updateProgress(e)
                    }), e
                }, this.upload = r.ajax(o)
            } else this.updateMessage(TYPO3.lang["file_upload.fileExtensionExpected"].replace(/\{0\}/g, this.dragUploader.filesExtensionsAllowed)), this.$row.addClass("error")
        }

        return e.prototype.updateMessage = function (e) {
            this.$progressMessage.text(e)
        }, e.prototype.removeProgress = function () {
            this.$progress && this.$progress.remove()
        }, e.prototype.uploadStart = function () {
            this.$progressPercentage.text("(0%)"), this.$progressBar.width("1%"), this.dragUploader.$trigger.trigger("uploadStart", [this])
        }, e.prototype.uploadError = function (e) {
            this.updateMessage(TYPO3.lang["file_upload.uploadFailed"].replace(/\{0\}/g, this.file.name));
            var t = r(e.responseText);
            t.is("t3err") ? this.$progressPercentage.text(t.text()) : this.$progressPercentage.text("(" + e.statusText + ")"), this.$row.addClass("error"), this.dragUploader.decrementQueueLength(), this.dragUploader.$trigger.trigger("uploadError", [this, e])
        }, e.prototype.updateProgress = function (e) {
            var t = Math.round(e.loaded / e.total * 100) + "%";
            this.$progressBar.outerWidth(t), this.$progressPercentage.text(t), this.dragUploader.$trigger.trigger("updateProgress", [this, t, e])
        }, e.prototype.uploadSuccess = function (e) {
            var t = this;
            e.upload && (this.dragUploader.decrementQueueLength(), this.$row.removeClass("uploading"), this.$fileName.text(e.upload[0].name), this.$progressPercentage.text(""), this.$progressMessage.text("100%"), this.$progressBar.outerWidth("100%"), e.upload[0].icon && this.$iconCol.html('<a href="#" class="t3js-contextmenutrigger" data-uid="' + e.upload[0].id + '" data-table="sys_file">' + e.upload[0].icon + "&nbsp;</span></a>"), this.dragUploader.irreObjectUid ? (u.addFileToIrre(this.dragUploader.irreObjectUid, e.upload[0]), setTimeout(function () {
                t.$row.remove(), 0 === r("tr", t.dragUploader.$fileList).length && (t.dragUploader.$fileList.hide(), t.dragUploader.$trigger.trigger("uploadSuccess", [t, e]))
            }, 3e3)) : setTimeout(function () {
                t.showFileInfo(e.upload[0]), t.dragUploader.$trigger.trigger("uploadSuccess", [t, e])
            }, 3e3))
        }, e.prototype.showFileInfo = function (e) {
            this.removeProgress();
            for (var t = 7; t < this.dragUploader.fileListColumnCount; t++) r("<td />").text("").appendTo(this.$row);
            r("<td />").text(e.extension.toUpperCase()).appendTo(this.$row), r("<td />").text(e.date).appendTo(this.$row), r("<td />").text(u.fileSizeAsString(e.size)).appendTo(this.$row);
            var i = "";
            e.permissions.read && (i += '<strong class="text-danger">' + TYPO3.lang["permissions.read"] + "</strong>"), e.permissions.write && (i += '<strong class="text-danger">' + TYPO3.lang["permissions.write"] + "</strong>"), r("<td />").html(i).appendTo(this.$row), r("<td />").text("-").appendTo(this.$row)
        }, e.prototype.checkAllowedExtensions = function () {
            if (!this.dragUploader.filesExtensionsAllowed) return !0;
            var e = this.file.name.split(".").pop(), t = this.dragUploader.filesExtensionsAllowed.split(",");
            return -1 !== r.inArray(e.toLowerCase(), t)
        }, e
    }(), u = function () {
        function e() {
        }

        return e.fileSizeAsString = function (e) {
            var t = e / 1024;
            return t > 1024 ? (t / 1024).toFixed(1) + " MB" : t.toFixed(1) + " KB"
        }, e.addFileToIrre = function (e, t) {
            window.inline.delayedImportElement(e, "sys_file", t.uid, "file")
        }, e.init = function () {
            var e = this.options;
            r.fn.extend({
                dragUploader: function (e) {
                    return this.each(function (t, i) {
                        var a = r(i), s = a.data("DragUploaderPlugin");
                        s || a.data("DragUploaderPlugin", s = new p(i)), "string" == typeof e && s[e]()
                    })
                }
            }), r(function () {
                r(".t3js-drag-uploader").dragUploader(e)
            })
        }, e
    }();
    t.initialize = function () {
        u.init(), void 0 !== TYPO3.settings && void 0 !== TYPO3.settings.RequireJS && void 0 !== TYPO3.settings.RequireJS.PostInitializationModules && void 0 !== TYPO3.settings.RequireJS.PostInitializationModules["TYPO3/CMS/Backend/DragUploader"] && r.each(TYPO3.settings.RequireJS.PostInitializationModules["TYPO3/CMS/Backend/DragUploader"], function (t, i) {
            e([i])
        })
    }, t.initialize()
});
