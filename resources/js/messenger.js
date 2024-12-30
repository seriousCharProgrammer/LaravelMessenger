/**
 * ------------------------------------------------------------
 * Reusable Messenger Component function
 * ------------------------------------------------------------
 */
function enableChatBoxLoader() {
    $(".overlay-chat").removeClass("d-none");
}

function disabledChatBoxLoader() {
    $(".overlay-chat").addClass("d-none");
}

function imagePreview(input, selector) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $(selector).attr("src", e.target.result);
        };
        reader.readAsDataURL(input.files[0]);
    }
}
let page = 1;
let noMoreDataSearch = false;
let searchTempValue = "";
let setSearchLoading = false;
function searchUsers(query) {
    if (query !== searchTempValue) {
        page = 1;
        noMoreDataSearch = false;
    }
    searchTempValue = query;
    if (!setSearchLoading && !noMoreDataSearch) {
        $.ajax({
            method: "GET",
            url: "messenger/search",
            data: {
                query: query,
                page: page,
            },
            beforeSend: function () {
                setSearchLoading = true;
                let loader = `  <div class="text-center search-loader">
            <div class="spinner-border text-primary " role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>`;
                $(".user_search_list_result").append(loader);
            },
            success: function (data) {
                if (data.records === "") {
                    $(".user_search_list_result").html(data.records);
                }
                $(".search-loader").remove();
                setSearchLoading = false;

                if (page < 2) {
                    $(".user_search_list_result").html(data.records);
                } else {
                    $(".user_search_list_result").append(data.records); //html(data.records)
                }
                noMoreDataSearch = page >= data?.last_page;
                if (!noMoreDataSearch) {
                    page++;
                }
            },
            error: function (xhr, status, error) {
                console.log(xhr);
            },
        });
    }
}

function debounce(callback, wait) {
    let timerId;
    return function (...args) {
        clearTimeout(timerId);
        timerId = setTimeout(() => {
            callback.apply(this, args);
        }, wait);
    };
}

function actionOnScroll(selector, callback, topScroll = false) {
    $(selector).on("scroll", function () {
        let element = $(this).get(0);
        const condition = topScroll
            ? element.scrollTop === 0
            : element.scrollTop + element.clientHeight >= element.scrollHeight;

        if (condition) {
            callback();
        }
    });
}

/***
 *
 * fetch id data of user and update the view
 *
 */

function IDinfo(id) {
    $.ajax({
        method: "GET",
        url: "messenger/id-info",
        data: { id: id },
        beforeSend: function () {
            NProgress.start();
            enableChatBoxLoader();
        },
        success: function (data) {
            $(".messenger-header").find("img").attr("src", data.fetch.avatar);
            $(".messenger-header").find("h4").text(data.fetch.name);
            $(".messenger-info-view .user_photo")
                .find("img")
                .attr("src", data.fetch.avatar);

            $(".messenger-info-view ").find("h3").text(data.fetch.name);
            $(".messenger-info-view .user_unique_name").text(
                data.fetch.username
            );
            NProgress.done();
            disabledChatBoxLoader();
        },

        error: function (xhr, status, error) {
            disabledChatBoxLoader();
        },
    });
}

/**
 * ------------------------------------------------------------
 * On Dom Load
 * ------------------------------------------------------------
 */

$(document).ready(function () {
    $("#select_file").change(function () {
        imagePreview(this, ".profile-image-preview");
    });
    const debouncedSearch = debounce(function () {
        const value = $(".user_search").val();
        searchUsers(value);
    }, 500);
    $(".user_search").on("keyup", function () {
        let query = $(this).val();
        if (query.length > 0) {
            debouncedSearch();
        }
    });
});
/**
 *
 * close search list if clicked ioutside it
 *
 */
$(document).on("click", (event) => {
    if (!$(event.target).closest(".user_search").length) {
        $("#search-input").val(""); // Use .val() to set the input value in jQuery
        $(".user_search_list_result").html("");
        page = 1;
    }
});

/**
 *
 *search pagtiantion
 *
 * */

actionOnScroll(".user_search_list_result", function () {
    let value = $(".user_search").val();
    searchUsers(value);
});

//click action for list-item
$("body").on("click", ".messenger-list-item", function () {
    const userId = $(this).data("id");
    IDinfo(userId);
});
