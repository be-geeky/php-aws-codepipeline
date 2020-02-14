<% if (products.enabled == 1 && products.count == 0 && categories.enabled == 1 && categories.count == 0 && cms.enabled == 1 && cms.count == 0 && suggests.enabled == 1 && suggests.count == 0) { %>
  <li>
    <span>No Result</span>
  </li>
<% } else if (products.count == 0 && categories.count == 0 && cms.count == 0 && suggests.enabled == 1 && suggests.count != 0) { %>
  <li class="qs-section">
    <span class="qs-option-name">Do you mean?</span>
  </li>
  <% _.each(suggests.docs, function(doc) { %>
  <li role="option" class="qs-option">
    <a href="<%= _.getSearchUrl() + '?q=' + doc %>">
      <div class="info">
        <span class="name"><%= doc %></span>
      </div>
      <div class="clearer"></div>
    </a>
  </li>
  <% }); %>
<% } else { %>
  <!-- suggestions -->
  <% if (suggests.count > 0) { %>
    <li class="qs-section">
      <span class="qs-option-name">Do you mean?</span>
    </li>
    <% _.each(suggests.docs, function(doc) { %>
      <li role="option" class="qs-option">
        <a href="<%= _.getSearchUrl() + '?q=' + doc %>">
          <div class="info">
            <span class="name"><%= doc %></span>
          </div>
          <div class="clearer"></div>
        </a>
    </li>
    <% }); %>
  <% } %>
<!-- products -->
  <% if (products.enabled == 1 && products.count > 0) { %>
    <li class="qs-section">
      <span class="qs-option-name">Products</span>
      <span aria-hidden="true" class="amount"><%- products.count %></span>
    </li>
    <% _.each(products.docs, function(doc) { %>
      <li role="option" class="qs-option">
        <a href="<% if (typeof doc.shortest_url != "undefined") { %><%- doc.shortest_url %><% } else { %><%- doc.url %><% } %>">
          <% if (doc.image) { %>
            <img src="<%- doc.image %>" alt="">
          <% } %>
          <div class="info">
            <span class="name"><%= doc.name %></span>
            <span class="price">
              <div class="price-box">
                <% if (doc.type_id == 'bundle') { %>
                  <p class="price-from">
                    <span class="price-label">From:&nbsp;</span>
                    <span class="price"><%= _.getFormattedPrice(doc.prices.min_price, doc.tax_class_id) %></span>
                  </p>
                  <p class="price-to">
                    <span class="price-label">To:&nbsp;</span>
                    <span class="price"><%= _.getFormattedPrice(doc.prices.max_price, doc.tax_class_id) %></span>
                  </p>
                <% } else if (doc.type_id == 'grouped') { %>
                  <p class="minimal-price">
                    <span class="price-label">Starting at:&nbsp;</span>
                    <span class="price"><%= _.getFormattedPrice(doc.prices.min_price, doc.tax_class_id) %></span>
                  </p>
                <% } else { %>
                  <% if (doc.prices.final_price < doc.prices.price) { %>
                    <p class="old-price">
                      <span class="price"><%= _.getFormattedPrice(doc.prices.price, doc.tax_class_id) %></span>
                    </p>
                    <p class="special-price">
                      <span class="price"><%= _.getFormattedPrice(doc.prices.final_price, doc.tax_class_id) %></span>
                    </p>
                  <% } else { %>
                    <span class="regular-price">
                      <span class="price"><%= _.getFormattedPrice(doc.prices.price, doc.tax_class_id) %></span>
                    </span>
                  <% } %>
                <% } %>
              </div>
            </span>
          </div>
          <div class="clearer"><!-- --></div>
        </a>
      </li>
    <% }); %>
  <% } %>
  <!-- categories -->
  <% if (categories.enabled == 1 && categories.count > 0) { %>
    <li class="qs-section">
      <span class="qs-option-name">Categories</span>
      <span aria-hidden="true" class="amount"><%- categories.count %></span>
    </li>
    <% _.each(categories.docs, function(doc) { %>
      <li role="option" class="qs-option">
        <a href="<%- doc.url %>">
          <div class="info">
            <span class="name"><%= doc.path %></span>
          </div>
          <div class="clearer"><!-- --></div>
        </a>
      </li>
    <% }); %>
  <% } %>
  <!-- cms -->
  <% if (cms.enabled == 1 && cms.count > 0) { %>
    <li class="qs-section">
      <span class="qs-option-name">Pages</span>
      <span aria-hidden="true" class="amount"><%- cms.count %></span>
    </li>
    <% _.each(cms.docs, function(doc) { %>
      <li role="option" class="qs-option">
        <a href="<%= _.getBaseUrl() + doc.identifier %>">
          <div class="info">
            <span class="name"><%= doc.title %></span>
          </div>
          <div class="clearer"><!-- --></div>
        </a>
      </li>
    <% }); %>
  <% } %>
<% } %>
