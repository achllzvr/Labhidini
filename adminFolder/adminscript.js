// profile modal logic
document.addEventListener("DOMContentLoaded", function () {
  var profileCard = document.getElementById("profileCard");
  var profileModal = new bootstrap.Modal(
    document.getElementById("profileModal")
  );
  var profileName = document.getElementById("profileName");
  var firstNameInput = document.getElementById("firstNameInput");
  var lastNameInput = document.getElementById("lastNameInput");
  var saveProfileBtn = document.getElementById("saveProfileBtn");

  var profileIcon = document.getElementById("profileIcon");
  var profileIconOverlay = profileIcon.parentElement.querySelector(
    ".profile-picture-overlay"
  );
  var profileIconModal = document.getElementById("profileIconModal");
  var profileIconOverlayModal = profileIconModal.parentElement.querySelector(
    ".profile-picture-overlay"
  );

  profileCard.addEventListener("click", function () {
    var nameParts = profileName.textContent.trim().split(" ");
    firstNameInput.value = nameParts[0] || "";
    lastNameInput.value = nameParts.slice(1).join(" ") || "";
    profileModal.show();
  });

  saveProfileBtn.addEventListener("click", function () {
    var first = firstNameInput.value.trim();
    var last = lastNameInput.value.trim();
    if (first && last) {
      profileName.textContent = first + " " + last;
      profileModal.hide();
    }
  });
});

// location modal logic
document.addEventListener("DOMContentLoaded", function () {
  var locationCard = document.getElementById("locationCard");
  var locationModal = new bootstrap.Modal(
    document.getElementById("locationModal")
  );
  if (locationCard) {
    locationCard.addEventListener("click", function () {
      locationModal.show();
    });
  }
});

// tooltip init
const tooltipTriggerList = document.querySelectorAll(
  '[data-bs-toggle="tooltip"]'
);
tooltipTriggerList.forEach((el) => new bootstrap.Tooltip(el));

// filter button animation
document.addEventListener("DOMContentLoaded", function () {
  var filterBtn = document.querySelector(".filter-btn");
  if (filterBtn) {
    filterBtn.addEventListener("mouseenter", function () {
      filterBtn.classList.remove("bounce-animate");
      void filterBtn.offsetWidth;
      filterBtn.classList.add("bounce-animate");
    });
    filterBtn.addEventListener("click", function () {
      filterBtn.classList.remove("bounce-animate");
      void filterBtn.offsetWidth;
      filterBtn.classList.add("bounce-animate");
    });
  }
});

// filter orders logic
document.addEventListener("DOMContentLoaded", function () {
  function getStatusFromRow(row) {
    const badge =
      row.querySelector(".glass-badge") ||
      row.querySelector(".badge");
    if (!badge) return "";
    return badge.textContent.trim();
  }
  function filterOrders() {
    const checkedStatuses = Array.from(
      document.querySelectorAll(".status-checkbox:checked")
    ).map((cb) => cb.value);
    const table = document.getElementById("ordersTable");
    if (!table) return;
    const rows = table.querySelectorAll("tbody tr");
    rows.forEach((row) => {
      const status = getStatusFromRow(row);
      row.style.display = checkedStatuses.includes(status) ? "" : "none";
    });
  }
  const applyBtn = document.getElementById("applyFilterBtn");
  if (applyBtn) {
    applyBtn.addEventListener("click", function () {
      filterOrders();
      const modal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById("filterModal")
      );
      modal.hide();
    });
  }
});

// about/terms/contact modal logic
document.addEventListener("DOMContentLoaded", function () {
  document.getElementById("aboutUsCard").addEventListener("click", function () {
    const aboutModal = bootstrap.Modal.getOrCreateInstance(
      document.getElementById("aboutUsModal")
    );
    aboutModal.show();
  });
  var termsCard = document.getElementById("termsCard");
  if (termsCard) {
    termsCard.addEventListener("click", function () {
      const termsModal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById("termsModal")
      );
      termsModal.show();
    });
  }
  document
    .getElementById("contactUsCard")
    .addEventListener("click", function () {
      const contactModal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById("contactUsModal")
      );
      contactModal.show();
    });
});

// services modal logic
document.addEventListener("DOMContentLoaded", function () {
  var servicesCard = document.getElementById("servicesCard");
  if (servicesCard) {
    servicesCard.addEventListener("click", function () {
      var modal = new bootstrap.Modal(document.getElementById("servicesModal"));
      modal.show();
    });
  }
});

// modal blur effect logic
document.addEventListener("DOMContentLoaded", function () {
  document.body.classList.remove("modal-blur-fadeout");
  var modals = document.querySelectorAll(".modal");
  modals.forEach(function (modal) {
    modal.addEventListener("show.bs.modal", function () {
      document.body.classList.remove("modal-blur-fadeout");
      document.body.classList.add("modal-blur");
    });
    modal.addEventListener("hide.bs.modal", function () {
      setTimeout(function () {
        if (!document.querySelectorAll(".modal.show").length) {
          document.body.classList.remove("modal-blur");
          document.body.classList.add("modal-blur-fadeout");
          setTimeout(function () {
            document.body.classList.remove("modal-blur-fadeout");
          }, 50);
        }
      }, 10);
    });
    modal.addEventListener("hidden.bs.modal", function () {
      if (!document.querySelector(".modal.show")) {
        document.body.classList.remove("modal-blur");
      }
    });
  });

  const observer = new MutationObserver(function () {
    if (document.body.classList.contains("modal-open")) {
      document.body.classList.remove("modal-open");
    }
    if (document.body.style.overflow === "hidden") {
      document.body.style.overflow = "";
    }
    if (
      document.body.style.paddingRight &&
      document.body.style.paddingRight !== "0px"
    ) {
      document.body.style.paddingRight = "";
    }
  });
  observer.observe(document.body, {
    attributes: true,
    attributeFilter: ["class", "style"],
  });
});

// new order list card click (redirect)
document.addEventListener("DOMContentLoaded", function () {
  var newOrderCard = document.getElementById("newOrderCard");
  if (newOrderCard) {
    newOrderCard.addEventListener("click", function () {
      window.location.href = "../adminFolder/newOrder.php";
    });
  }
});

// order list card click (redirect)
document.addEventListener("DOMContentLoaded", function () {
  var orderListCard = document.getElementById("orderListCard");
  if (orderListCard) {
    orderListCard.addEventListener("click", function () {
      window.location.href = "../adminFolder/orderList.php";
    });
  }
});

// edit services button click (redirect)
document.addEventListener("DOMContentLoaded", function () {
  var editServicesBtn = document.getElementById("editServicesBtn");
  if (editServicesBtn) {
    editServicesBtn.addEventListener("click", function () {
      window.location.href = "../adminFolder/services.php";
    });
  }
});

// customer list card click (redirect)
document.addEventListener("DOMContentLoaded", function () {
  var customerListCard = document.getElementById("customerListCard");
  if (customerListCard) {
    customerListCard.addEventListener("click", function () {
      window.location.href = "../adminFolder/customerList.php";
    });
  }
});

// sales card click (redirect)
document.addEventListener("DOMContentLoaded", function () {
  var salesCard = document.getElementById("salesCard");
  if (salesCard) {
    salesCard.addEventListener("click", function () {
      window.location.href = "../adminFolder/sales.php";
    });
  }
});

// Create the glow effect in the background
const glowEffect = document.createElement('div');
glowEffect.id = 'background-glow';
document.body.appendChild(glowEffect);

// Update the glow position on mouse move
document.addEventListener('mousemove', (event) => {
    const x = event.clientX;
    const y = event.clientY;
    glowEffect.style.background = `radial-gradient(circle at ${x}px ${y}px, rgb(178, 221, 218), transparent 14%)`;
});
