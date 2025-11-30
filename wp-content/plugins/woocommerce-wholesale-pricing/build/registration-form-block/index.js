wp.blocks.registerBlockType("wholesale/wholesale-registration", {
  title: "Wholesale Registration",
  icon: "editor-table",
  category: "common",
  edit: function () {
    return wp.element.createElement("p", null, 'The wholesale Registration Form Will Render On Frontend')
  },
  save: function () {
    return null
  }
})
