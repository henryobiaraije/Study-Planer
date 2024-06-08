
Object.defineProperty(exports, "__esModule", { value: true });

const {
  Decimal,
  objectEnumValues,
  makeStrictEnum,
  Public,
  getRuntime,
} = require('./runtime/index-browser.js')


const Prisma = {}

exports.Prisma = Prisma
exports.$Enums = {}

/**
 * Prisma Client JS version: 5.15.0
 * Query Engine version: 12e25d8d06f6ea5a0252864dd9a03b1bb51f3022
 */
Prisma.prismaVersion = {
  client: "5.15.0",
  engine: "12e25d8d06f6ea5a0252864dd9a03b1bb51f3022"
}

Prisma.PrismaClientKnownRequestError = () => {
  const runtimeName = getRuntime().prettyName;
  throw new Error(`PrismaClientKnownRequestError is unable to run in this browser environment, or has been bundled for the browser (running in ${runtimeName}).
In case this error is unexpected for you, please report it in https://pris.ly/prisma-prisma-bug-report`,
)};
Prisma.PrismaClientUnknownRequestError = () => {
  const runtimeName = getRuntime().prettyName;
  throw new Error(`PrismaClientUnknownRequestError is unable to run in this browser environment, or has been bundled for the browser (running in ${runtimeName}).
In case this error is unexpected for you, please report it in https://pris.ly/prisma-prisma-bug-report`,
)}
Prisma.PrismaClientRustPanicError = () => {
  const runtimeName = getRuntime().prettyName;
  throw new Error(`PrismaClientRustPanicError is unable to run in this browser environment, or has been bundled for the browser (running in ${runtimeName}).
In case this error is unexpected for you, please report it in https://pris.ly/prisma-prisma-bug-report`,
)}
Prisma.PrismaClientInitializationError = () => {
  const runtimeName = getRuntime().prettyName;
  throw new Error(`PrismaClientInitializationError is unable to run in this browser environment, or has been bundled for the browser (running in ${runtimeName}).
In case this error is unexpected for you, please report it in https://pris.ly/prisma-prisma-bug-report`,
)}
Prisma.PrismaClientValidationError = () => {
  const runtimeName = getRuntime().prettyName;
  throw new Error(`PrismaClientValidationError is unable to run in this browser environment, or has been bundled for the browser (running in ${runtimeName}).
In case this error is unexpected for you, please report it in https://pris.ly/prisma-prisma-bug-report`,
)}
Prisma.NotFoundError = () => {
  const runtimeName = getRuntime().prettyName;
  throw new Error(`NotFoundError is unable to run in this browser environment, or has been bundled for the browser (running in ${runtimeName}).
In case this error is unexpected for you, please report it in https://pris.ly/prisma-prisma-bug-report`,
)}
Prisma.Decimal = Decimal

/**
 * Re-export of sql-template-tag
 */
Prisma.sql = () => {
  const runtimeName = getRuntime().prettyName;
  throw new Error(`sqltag is unable to run in this browser environment, or has been bundled for the browser (running in ${runtimeName}).
In case this error is unexpected for you, please report it in https://pris.ly/prisma-prisma-bug-report`,
)}
Prisma.empty = () => {
  const runtimeName = getRuntime().prettyName;
  throw new Error(`empty is unable to run in this browser environment, or has been bundled for the browser (running in ${runtimeName}).
In case this error is unexpected for you, please report it in https://pris.ly/prisma-prisma-bug-report`,
)}
Prisma.join = () => {
  const runtimeName = getRuntime().prettyName;
  throw new Error(`join is unable to run in this browser environment, or has been bundled for the browser (running in ${runtimeName}).
In case this error is unexpected for you, please report it in https://pris.ly/prisma-prisma-bug-report`,
)}
Prisma.raw = () => {
  const runtimeName = getRuntime().prettyName;
  throw new Error(`raw is unable to run in this browser environment, or has been bundled for the browser (running in ${runtimeName}).
In case this error is unexpected for you, please report it in https://pris.ly/prisma-prisma-bug-report`,
)}
Prisma.validator = Public.validator

/**
* Extensions
*/
Prisma.getExtensionContext = () => {
  const runtimeName = getRuntime().prettyName;
  throw new Error(`Extensions.getExtensionContext is unable to run in this browser environment, or has been bundled for the browser (running in ${runtimeName}).
In case this error is unexpected for you, please report it in https://pris.ly/prisma-prisma-bug-report`,
)}
Prisma.defineExtension = () => {
  const runtimeName = getRuntime().prettyName;
  throw new Error(`Extensions.defineExtension is unable to run in this browser environment, or has been bundled for the browser (running in ${runtimeName}).
In case this error is unexpected for you, please report it in https://pris.ly/prisma-prisma-bug-report`,
)}

/**
 * Shorthand utilities for JSON filtering
 */
Prisma.DbNull = objectEnumValues.instances.DbNull
Prisma.JsonNull = objectEnumValues.instances.JsonNull
Prisma.AnyNull = objectEnumValues.instances.AnyNull

Prisma.NullTypes = {
  DbNull: objectEnumValues.classes.DbNull,
  JsonNull: objectEnumValues.classes.JsonNull,
  AnyNull: objectEnumValues.classes.AnyNull
}

/**
 * Enums
 */

exports.Prisma.TransactionIsolationLevel = makeStrictEnum({
  ReadUncommitted: 'ReadUncommitted',
  ReadCommitted: 'ReadCommitted',
  RepeatableRead: 'RepeatableRead',
  Serializable: 'Serializable'
});

exports.Prisma.Deck_groupsScalarFieldEnum = {
  id: 'id',
  name: 'name',
  created_at: 'created_at',
  updated_at: 'updated_at',
  deleted_at: 'deleted_at'
};

exports.Prisma.PhinxlogScalarFieldEnum = {
  version: 'version',
  migration_name: 'migration_name',
  start_time: 'start_time',
  end_time: 'end_time',
  breakpoint: 'breakpoint'
};

exports.Prisma.Wp_a_mp_knowledge_based_search_product_catalogueScalarFieldEnum = {
  cat_id: 'cat_id',
  cat_name: 'cat_name',
  cat_product_id: 'cat_product_id',
  cat_sku_number: 'cat_sku_number',
  cat_link_buy: 'cat_link_buy',
  cat_link_product: 'cat_link_product',
  cat_link_product_image: 'cat_link_product_image',
  cat_link_category_primary: 'cat_link_category_primary',
  cat_link_category_secondary: 'cat_link_category_secondary',
  cat_description_long: 'cat_description_long',
  cat_description_short: 'cat_description_short',
  cat_discount_currency: 'cat_discount_currency',
  cat_discount_currency_type: 'cat_discount_currency_type',
  cat_keywords: 'cat_keywords',
  cat_m1: 'cat_m1',
  cat_modification: 'cat_modification',
  cat_pixel: 'cat_pixel',
  cat_price_currency: 'cat_price_currency',
  cat_price_retail: 'cat_price_retail',
  cat_price_sale: 'cat_price_sale',
  cat_shipping_availability: 'cat_shipping_availability',
  cat_details: 'cat_details',
  cat_location: 'cat_location',
  cat_post_id: 'cat_post_id',
  deleted: 'deleted',
  posted: 'posted'
};

exports.Prisma.Wp_a_mp_knowledge_based_search_sqlScalarFieldEnum = {
  id: 'id',
  gen_sql: 'gen_sql',
  args: 'args',
  info: 'info',
  is_filter: 'is_filter',
  gen_key: 'gen_key',
  datetime: 'datetime',
  deleted: 'deleted'
};

exports.Prisma.Wp_actionscheduler_actionsScalarFieldEnum = {
  action_id: 'action_id',
  hook: 'hook',
  status: 'status',
  scheduled_date_gmt: 'scheduled_date_gmt',
  scheduled_date_local: 'scheduled_date_local',
  args: 'args',
  schedule: 'schedule',
  group_id: 'group_id',
  attempts: 'attempts',
  last_attempt_gmt: 'last_attempt_gmt',
  last_attempt_local: 'last_attempt_local',
  claim_id: 'claim_id',
  extended_args: 'extended_args'
};

exports.Prisma.Wp_actionscheduler_claimsScalarFieldEnum = {
  claim_id: 'claim_id',
  date_created_gmt: 'date_created_gmt'
};

exports.Prisma.Wp_actionscheduler_groupsScalarFieldEnum = {
  group_id: 'group_id',
  slug: 'slug'
};

exports.Prisma.Wp_actionscheduler_logsScalarFieldEnum = {
  log_id: 'log_id',
  action_id: 'action_id',
  message: 'message',
  log_date_gmt: 'log_date_gmt',
  log_date_local: 'log_date_local'
};

exports.Prisma.Wp_arm_free_membersScalarFieldEnum = {
  arm_free_mmember_id: 'arm_free_mmember_id',
  arm_free_mmember_user_id: 'arm_free_mmember_user_id',
  arm_free_mmember_user_login: 'arm_free_mmember_user_login',
  arm_free_mmember_user_email: 'arm_free_mmember_user_email',
  deleted: 'deleted'
};

exports.Prisma.Wp_awesome_auction_password_resetScalarFieldEnum = {
  id: 'id',
  email: 'email',
  code: 'code',
  used: 'used',
  datetime: 'datetime',
  timestamp: 'timestamp',
  deleted: 'deleted'
};

exports.Prisma.Wp_aysquiz_answersScalarFieldEnum = {
  id: 'id',
  question_id: 'question_id',
  answer: 'answer',
  image: 'image',
  correct: 'correct',
  ordering: 'ordering',
  weight: 'weight',
  placeholder: 'placeholder'
};

exports.Prisma.Wp_aysquiz_categoriesScalarFieldEnum = {
  id: 'id',
  title: 'title',
  description: 'description',
  published: 'published'
};

exports.Prisma.Wp_aysquiz_questionsScalarFieldEnum = {
  id: 'id',
  category_id: 'category_id',
  question: 'question',
  question_image: 'question_image',
  wrong_answer_text: 'wrong_answer_text',
  right_answer_text: 'right_answer_text',
  question_hint: 'question_hint',
  explanation: 'explanation',
  type: 'type',
  published: 'published',
  create_date: 'create_date',
  not_influence_to_score: 'not_influence_to_score',
  weight: 'weight',
  options: 'options'
};

exports.Prisma.Wp_aysquiz_quizcategoriesScalarFieldEnum = {
  id: 'id',
  title: 'title',
  description: 'description',
  published: 'published'
};

exports.Prisma.Wp_aysquiz_quizesScalarFieldEnum = {
  id: 'id',
  title: 'title',
  description: 'description',
  quiz_image: 'quiz_image',
  quiz_category_id: 'quiz_category_id',
  question_ids: 'question_ids',
  ordering: 'ordering',
  published: 'published',
  options: 'options',
  intervals: 'intervals'
};

exports.Prisma.Wp_aysquiz_ratesScalarFieldEnum = {
  id: 'id',
  quiz_id: 'quiz_id',
  user_id: 'user_id',
  user_ip: 'user_ip',
  user_name: 'user_name',
  user_email: 'user_email',
  user_phone: 'user_phone',
  rate_date: 'rate_date',
  score: 'score',
  review: 'review',
  options: 'options'
};

exports.Prisma.Wp_aysquiz_reportsScalarFieldEnum = {
  id: 'id',
  quiz_id: 'quiz_id',
  user_id: 'user_id',
  user_ip: 'user_ip',
  user_name: 'user_name',
  user_email: 'user_email',
  user_phone: 'user_phone',
  start_date: 'start_date',
  end_date: 'end_date',
  duration: 'duration',
  score: 'score',
  options: 'options',
  read: 'read'
};

exports.Prisma.Wp_aysquiz_settingsScalarFieldEnum = {
  id: 'id',
  meta_key: 'meta_key',
  meta_value: 'meta_value',
  note: 'note',
  options: 'options'
};

exports.Prisma.Wp_aysquiz_themesScalarFieldEnum = {
  id: 'id',
  title: 'title',
  border_radius: 'border_radius',
  show_result_presentage: 'show_result_presentage',
  show_result_answers: 'show_result_answers',
  buttons_color: 'buttons_color',
  buttons_bg_color: 'buttons_bg_color',
  buttons_hover_color: 'buttons_hover_color',
  buttons_hover_bg_color: 'buttons_hover_bg_color',
  quiz_title_color: 'quiz_title_color',
  quiz_description_color: 'quiz_description_color',
  question_color: 'question_color',
  question_bg_color: 'question_bg_color',
  question_answer_color: 'question_answer_color',
  question_answer_bg_color: 'question_answer_bg_color',
  question_answer_hover_color: 'question_answer_hover_color',
  question_answer_hover_bg_color: 'question_answer_hover_bg_color',
  question_correct_answer_bg_color: 'question_correct_answer_bg_color',
  question_incorrect_answer_bg_color: 'question_incorrect_answer_bg_color',
  pagination_bg_color: 'pagination_bg_color',
  pagination_color: 'pagination_color'
};

exports.Prisma.Wp_commentmetaScalarFieldEnum = {
  meta_id: 'meta_id',
  comment_id: 'comment_id',
  meta_key: 'meta_key',
  meta_value: 'meta_value'
};

exports.Prisma.Wp_commentsScalarFieldEnum = {
  comment_ID: 'comment_ID',
  comment_post_ID: 'comment_post_ID',
  comment_author: 'comment_author',
  comment_author_email: 'comment_author_email',
  comment_author_url: 'comment_author_url',
  comment_author_IP: 'comment_author_IP',
  comment_date: 'comment_date',
  comment_date_gmt: 'comment_date_gmt',
  comment_content: 'comment_content',
  comment_karma: 'comment_karma',
  comment_approved: 'comment_approved',
  comment_agent: 'comment_agent',
  comment_type: 'comment_type',
  comment_parent: 'comment_parent',
  user_id: 'user_id'
};

exports.Prisma.Wp_e_eventsScalarFieldEnum = {
  id: 'id',
  event_data: 'event_data',
  created_at: 'created_at'
};

exports.Prisma.Wp_fbvScalarFieldEnum = {
  id: 'id',
  name: 'name',
  parent: 'parent',
  type: 'type',
  ord: 'ord',
  created_by: 'created_by'
};

exports.Prisma.Wp_fbv_attachment_folderScalarFieldEnum = {
  folder_id: 'folder_id',
  attachment_id: 'attachment_id'
};

exports.Prisma.Wp_ff_scheduled_actionsScalarFieldEnum = {
  id: 'id',
  action: 'action',
  form_id: 'form_id',
  origin_id: 'origin_id',
  feed_id: 'feed_id',
  type: 'type',
  status: 'status',
  data: 'data',
  note: 'note',
  retry_count: 'retry_count',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_fluentform_draft_submissionsScalarFieldEnum = {
  id: 'id',
  form_id: 'form_id',
  hash: 'hash',
  type: 'type',
  step_completed: 'step_completed',
  user_id: 'user_id',
  response: 'response',
  source_url: 'source_url',
  browser: 'browser',
  device: 'device',
  ip: 'ip',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_fluentform_entry_detailsScalarFieldEnum = {
  id: 'id',
  form_id: 'form_id',
  submission_id: 'submission_id',
  field_name: 'field_name',
  sub_field_name: 'sub_field_name',
  field_value: 'field_value'
};

exports.Prisma.Wp_fluentform_form_analyticsScalarFieldEnum = {
  id: 'id',
  form_id: 'form_id',
  user_id: 'user_id',
  source_url: 'source_url',
  platform: 'platform',
  browser: 'browser',
  city: 'city',
  country: 'country',
  ip: 'ip',
  count: 'count',
  created_at: 'created_at'
};

exports.Prisma.Wp_fluentform_form_metaScalarFieldEnum = {
  id: 'id',
  form_id: 'form_id',
  meta_key: 'meta_key',
  value: 'value'
};

exports.Prisma.Wp_fluentform_formsScalarFieldEnum = {
  id: 'id',
  title: 'title',
  status: 'status',
  appearance_settings: 'appearance_settings',
  form_fields: 'form_fields',
  has_payment: 'has_payment',
  type: 'type',
  conditions: 'conditions',
  created_by: 'created_by',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_fluentform_logsScalarFieldEnum = {
  id: 'id',
  parent_source_id: 'parent_source_id',
  source_type: 'source_type',
  source_id: 'source_id',
  component: 'component',
  status: 'status',
  title: 'title',
  description: 'description',
  created_at: 'created_at'
};

exports.Prisma.Wp_fluentform_order_itemsScalarFieldEnum = {
  id: 'id',
  form_id: 'form_id',
  submission_id: 'submission_id',
  type: 'type',
  parent_holder: 'parent_holder',
  billing_interval: 'billing_interval',
  item_name: 'item_name',
  quantity: 'quantity',
  item_price: 'item_price',
  line_total: 'line_total',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_fluentform_submission_metaScalarFieldEnum = {
  id: 'id',
  response_id: 'response_id',
  form_id: 'form_id',
  meta_key: 'meta_key',
  value: 'value',
  status: 'status',
  user_id: 'user_id',
  name: 'name',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_fluentform_submissionsScalarFieldEnum = {
  id: 'id',
  form_id: 'form_id',
  serial_number: 'serial_number',
  response: 'response',
  source_url: 'source_url',
  user_id: 'user_id',
  status: 'status',
  is_favourite: 'is_favourite',
  browser: 'browser',
  device: 'device',
  ip: 'ip',
  city: 'city',
  country: 'country',
  payment_status: 'payment_status',
  payment_method: 'payment_method',
  payment_type: 'payment_type',
  currency: 'currency',
  payment_total: 'payment_total',
  total_paid: 'total_paid',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_fluentform_subscriptionsScalarFieldEnum = {
  id: 'id',
  submission_id: 'submission_id',
  form_id: 'form_id',
  payment_total: 'payment_total',
  item_name: 'item_name',
  plan_name: 'plan_name',
  parent_transaction_id: 'parent_transaction_id',
  billing_interval: 'billing_interval',
  trial_days: 'trial_days',
  initial_amount: 'initial_amount',
  quantity: 'quantity',
  recurring_amount: 'recurring_amount',
  bill_times: 'bill_times',
  bill_count: 'bill_count',
  vendor_customer_id: 'vendor_customer_id',
  vendor_subscription_id: 'vendor_subscription_id',
  vendor_plan_id: 'vendor_plan_id',
  status: 'status',
  initial_tax_label: 'initial_tax_label',
  initial_tax: 'initial_tax',
  recurring_tax_label: 'recurring_tax_label',
  recurring_tax: 'recurring_tax',
  element_id: 'element_id',
  note: 'note',
  original_plan: 'original_plan',
  vendor_response: 'vendor_response',
  expiration_at: 'expiration_at',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_fluentform_transactionsScalarFieldEnum = {
  id: 'id',
  transaction_hash: 'transaction_hash',
  payer_name: 'payer_name',
  payer_email: 'payer_email',
  billing_address: 'billing_address',
  shipping_address: 'shipping_address',
  form_id: 'form_id',
  user_id: 'user_id',
  submission_id: 'submission_id',
  subscription_id: 'subscription_id',
  transaction_type: 'transaction_type',
  payment_method: 'payment_method',
  card_last_4: 'card_last_4',
  card_brand: 'card_brand',
  charge_id: 'charge_id',
  payment_total: 'payment_total',
  status: 'status',
  currency: 'currency',
  payment_mode: 'payment_mode',
  payment_note: 'payment_note',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_frm_fieldsScalarFieldEnum = {
  id: 'id',
  field_key: 'field_key',
  name: 'name',
  description: 'description',
  type: 'type',
  default_value: 'default_value',
  options: 'options',
  field_order: 'field_order',
  required: 'required',
  field_options: 'field_options',
  form_id: 'form_id',
  created_at: 'created_at'
};

exports.Prisma.Wp_frm_formsScalarFieldEnum = {
  id: 'id',
  form_key: 'form_key',
  name: 'name',
  description: 'description',
  parent_form_id: 'parent_form_id',
  logged_in: 'logged_in',
  editable: 'editable',
  is_template: 'is_template',
  default_template: 'default_template',
  status: 'status',
  options: 'options',
  created_at: 'created_at'
};

exports.Prisma.Wp_frm_item_metasScalarFieldEnum = {
  id: 'id',
  meta_value: 'meta_value',
  field_id: 'field_id',
  item_id: 'item_id',
  created_at: 'created_at'
};

exports.Prisma.Wp_frm_itemsScalarFieldEnum = {
  id: 'id',
  item_key: 'item_key',
  name: 'name',
  description: 'description',
  ip: 'ip',
  form_id: 'form_id',
  post_id: 'post_id',
  user_id: 'user_id',
  parent_item_id: 'parent_item_id',
  is_draft: 'is_draft',
  updated_by: 'updated_by',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_h5p_contentsScalarFieldEnum = {
  id: 'id',
  created_at: 'created_at',
  updated_at: 'updated_at',
  user_id: 'user_id',
  title: 'title',
  library_id: 'library_id',
  parameters: 'parameters',
  filtered: 'filtered',
  slug: 'slug',
  embed_type: 'embed_type',
  disable: 'disable',
  content_type: 'content_type',
  authors: 'authors',
  source: 'source',
  year_from: 'year_from',
  year_to: 'year_to',
  license: 'license',
  license_version: 'license_version',
  license_extras: 'license_extras',
  author_comments: 'author_comments',
  changes: 'changes',
  default_language: 'default_language',
  a11y_title: 'a11y_title'
};

exports.Prisma.Wp_h5p_contents_librariesScalarFieldEnum = {
  content_id: 'content_id',
  library_id: 'library_id',
  dependency_type: 'dependency_type',
  weight: 'weight',
  drop_css: 'drop_css'
};

exports.Prisma.Wp_h5p_contents_tagsScalarFieldEnum = {
  content_id: 'content_id',
  tag_id: 'tag_id'
};

exports.Prisma.Wp_h5p_contents_user_dataScalarFieldEnum = {
  content_id: 'content_id',
  user_id: 'user_id',
  sub_content_id: 'sub_content_id',
  data_id: 'data_id',
  data: 'data',
  preload: 'preload',
  invalidate: 'invalidate',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_h5p_countersScalarFieldEnum = {
  type: 'type',
  library_name: 'library_name',
  library_version: 'library_version',
  num: 'num'
};

exports.Prisma.Wp_h5p_eventsScalarFieldEnum = {
  id: 'id',
  user_id: 'user_id',
  created_at: 'created_at',
  type: 'type',
  sub_type: 'sub_type',
  content_id: 'content_id',
  content_title: 'content_title',
  library_name: 'library_name',
  library_version: 'library_version'
};

exports.Prisma.Wp_h5p_librariesScalarFieldEnum = {
  id: 'id',
  created_at: 'created_at',
  updated_at: 'updated_at',
  name: 'name',
  title: 'title',
  major_version: 'major_version',
  minor_version: 'minor_version',
  patch_version: 'patch_version',
  runnable: 'runnable',
  restricted: 'restricted',
  fullscreen: 'fullscreen',
  embed_types: 'embed_types',
  preloaded_js: 'preloaded_js',
  preloaded_css: 'preloaded_css',
  drop_library_css: 'drop_library_css',
  semantics: 'semantics',
  tutorial_url: 'tutorial_url',
  has_icon: 'has_icon',
  metadata_settings: 'metadata_settings',
  add_to: 'add_to'
};

exports.Prisma.Wp_h5p_libraries_cachedassetsScalarFieldEnum = {
  library_id: 'library_id',
  hash: 'hash'
};

exports.Prisma.Wp_h5p_libraries_hub_cacheScalarFieldEnum = {
  id: 'id',
  machine_name: 'machine_name',
  major_version: 'major_version',
  minor_version: 'minor_version',
  patch_version: 'patch_version',
  h5p_major_version: 'h5p_major_version',
  h5p_minor_version: 'h5p_minor_version',
  title: 'title',
  summary: 'summary',
  description: 'description',
  icon: 'icon',
  created_at: 'created_at',
  updated_at: 'updated_at',
  is_recommended: 'is_recommended',
  popularity: 'popularity',
  screenshots: 'screenshots',
  license: 'license',
  example: 'example',
  tutorial: 'tutorial',
  keywords: 'keywords',
  categories: 'categories',
  owner: 'owner'
};

exports.Prisma.Wp_h5p_libraries_languagesScalarFieldEnum = {
  library_id: 'library_id',
  language_code: 'language_code',
  translation: 'translation'
};

exports.Prisma.Wp_h5p_libraries_librariesScalarFieldEnum = {
  library_id: 'library_id',
  required_library_id: 'required_library_id',
  dependency_type: 'dependency_type'
};

exports.Prisma.Wp_h5p_resultsScalarFieldEnum = {
  id: 'id',
  content_id: 'content_id',
  user_id: 'user_id',
  score: 'score',
  max_score: 'max_score',
  opened: 'opened',
  finished: 'finished',
  time: 'time'
};

exports.Prisma.Wp_h5p_tagsScalarFieldEnum = {
  id: 'id',
  name: 'name'
};

exports.Prisma.Wp_h5p_tmpfilesScalarFieldEnum = {
  id: 'id',
  path: 'path',
  created_at: 'created_at'
};

exports.Prisma.Wp_linksScalarFieldEnum = {
  link_id: 'link_id',
  link_url: 'link_url',
  link_name: 'link_name',
  link_image: 'link_image',
  link_target: 'link_target',
  link_description: 'link_description',
  link_visible: 'link_visible',
  link_owner: 'link_owner',
  link_rating: 'link_rating',
  link_updated: 'link_updated',
  link_rel: 'link_rel',
  link_notes: 'link_notes',
  link_rss: 'link_rss'
};

exports.Prisma.Wp_lte_device_serial_dataScalarFieldEnum = {
  id: 'id',
  serial_suffix: 'serial_suffix',
  sales_number: 'sales_number',
  model_number: 'model_number',
  machine_number: 'machine_number',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_mp_student_teacher_manager_attendanceScalarFieldEnum = {
  mp_id: 'mp_id',
  mp_student_id: 'mp_student_id',
  mp_teacher_id: 'mp_teacher_id',
  mp_present: 'mp_present',
  mp_date_marked: 'mp_date_marked',
  mp_datetime: 'mp_datetime',
  mp_datetime_marked_again: 'mp_datetime_marked_again',
  mp_deleted: 'mp_deleted'
};

exports.Prisma.Wp_mp_student_teacher_manager_billsScalarFieldEnum = {
  mp_id: 'mp_id',
  mp_bill_name: 'mp_bill_name',
  mp_bill_teacher_id: 'mp_bill_teacher_id',
  mp_bill_points: 'mp_bill_points',
  mp_bill_description: 'mp_bill_description',
  mp_bill_deleted: 'mp_bill_deleted',
  mp_bill_modified_datetime: 'mp_bill_modified_datetime',
  mp_bill_created_date: 'mp_bill_created_date'
};

exports.Prisma.Wp_mp_student_teacher_manager_issued_billsScalarFieldEnum = {
  mp_id: 'mp_id',
  mp_bill_id: 'mp_bill_id',
  mp_student_id: 'mp_student_id',
  mp_teacher_id: 'mp_teacher_id',
  mp_fulfilled: 'mp_fulfilled',
  mp_issued_datetime: 'mp_issued_datetime',
  mp_fulfilled_datetime: 'mp_fulfilled_datetime',
  mp_deleted: 'mp_deleted'
};

exports.Prisma.Wp_mp_student_teacher_manager_optionsScalarFieldEnum = {
  mp_option_id: 'mp_option_id',
  mp_option_const: 'mp_option_const',
  mp_option_value: 'mp_option_value'
};

exports.Prisma.Wp_mp_student_teacher_manager_point_typesScalarFieldEnum = {
  mp_point_type_id: 'mp_point_type_id',
  mp_point_type_name: 'mp_point_type_name',
  mp_point_type_user_id: 'mp_point_type_user_id',
  mp_point_type_key: 'mp_point_type_key',
  mp_point_type_range_start: 'mp_point_type_range_start',
  mp_point_type_range_stop: 'mp_point_type_range_stop',
  mp_point_type_datetime: 'mp_point_type_datetime',
  mp_point_type_deleted: 'mp_point_type_deleted'
};

exports.Prisma.Wp_mp_student_teacher_manager_pointsScalarFieldEnum = {
  mp_p_id: 'mp_p_id',
  mp_student_id: 'mp_student_id',
  mp_teacher_id: 'mp_teacher_id',
  mp_point_type_display: 'mp_point_type_display',
  mp_point_type_name: 'mp_point_type_name',
  mp_point: 'mp_point',
  mp_date: 'mp_date',
  mp_deleted: 'mp_deleted'
};

exports.Prisma.Wp_mp_student_teacher_manager_productsScalarFieldEnum = {
  mp_product_id: 'mp_product_id',
  mp_product_teacher_id: 'mp_product_teacher_id',
  mp_product_title: 'mp_product_title',
  mp_product_desctiption: 'mp_product_desctiption',
  mp_product_image: 'mp_product_image',
  mp_product_price: 'mp_product_price',
  mp_product_created_datetime: 'mp_product_created_datetime',
  mp_product_id_deleted: 'mp_product_id_deleted'
};

exports.Prisma.Wp_mp_student_teacher_manager_products_boughtScalarFieldEnum = {
  mp_product_bought_id: 'mp_product_bought_id',
  mp_product_bought_teacher_id: 'mp_product_bought_teacher_id',
  mp_product_bought_student_id: 'mp_product_bought_student_id',
  mp_product_bought_title: 'mp_product_bought_title',
  mp_product_bought_desctiption: 'mp_product_bought_desctiption',
  mp_product_bought_image: 'mp_product_bought_image',
  mp_product_bought_price: 'mp_product_bought_price',
  mp_product_bought_bought_datetime: 'mp_product_bought_bought_datetime',
  mp_product_bought_id_deleted: 'mp_product_bought_id_deleted'
};

exports.Prisma.Wp_mp_student_teacher_manager_teachers_studentsScalarFieldEnum = {
  mp_ts_id: 'mp_ts_id',
  mp_teacher_id: 'mp_teacher_id',
  mp_studets_ids_json: 'mp_studets_ids_json',
  mp_deleted: 'mp_deleted'
};

exports.Prisma.Wp_mp_student_teacher_manager_user_imageScalarFieldEnum = {
  mp_id: 'mp_id',
  mp_image_u_id: 'mp_image_u_id',
  mp_image_attr_id: 'mp_image_attr_id',
  mp_image_deleted: 'mp_image_deleted'
};

exports.Prisma.Wp_mycred_logScalarFieldEnum = {
  id: 'id',
  ref: 'ref',
  ref_id: 'ref_id',
  user_id: 'user_id',
  creds: 'creds',
  ctype: 'ctype',
  time: 'time',
  entry: 'entry',
  data: 'data'
};

exports.Prisma.Wp_newsletterScalarFieldEnum = {
  name: 'name',
  email: 'email',
  token: 'token',
  language: 'language',
  status: 'status',
  id: 'id',
  profile: 'profile',
  created: 'created',
  updated: 'updated',
  last_activity: 'last_activity',
  followup_step: 'followup_step',
  followup_time: 'followup_time',
  followup: 'followup',
  surname: 'surname',
  sex: 'sex',
  feed_time: 'feed_time',
  feed: 'feed',
  referrer: 'referrer',
  ip: 'ip',
  wp_user_id: 'wp_user_id',
  http_referer: 'http_referer',
  geo: 'geo',
  country: 'country',
  region: 'region',
  city: 'city',
  bounce_type: 'bounce_type',
  bounce_time: 'bounce_time',
  unsub_email_id: 'unsub_email_id',
  unsub_time: 'unsub_time',
  list_1: 'list_1',
  list_2: 'list_2',
  list_3: 'list_3',
  list_4: 'list_4',
  list_5: 'list_5',
  list_6: 'list_6',
  list_7: 'list_7',
  list_8: 'list_8',
  list_9: 'list_9',
  list_10: 'list_10',
  list_11: 'list_11',
  list_12: 'list_12',
  list_13: 'list_13',
  list_14: 'list_14',
  list_15: 'list_15',
  list_16: 'list_16',
  list_17: 'list_17',
  list_18: 'list_18',
  list_19: 'list_19',
  list_20: 'list_20',
  list_21: 'list_21',
  list_22: 'list_22',
  list_23: 'list_23',
  list_24: 'list_24',
  list_25: 'list_25',
  list_26: 'list_26',
  list_27: 'list_27',
  list_28: 'list_28',
  list_29: 'list_29',
  list_30: 'list_30',
  list_31: 'list_31',
  list_32: 'list_32',
  list_33: 'list_33',
  list_34: 'list_34',
  list_35: 'list_35',
  list_36: 'list_36',
  list_37: 'list_37',
  list_38: 'list_38',
  list_39: 'list_39',
  list_40: 'list_40',
  profile_1: 'profile_1',
  profile_2: 'profile_2',
  profile_3: 'profile_3',
  profile_4: 'profile_4',
  profile_5: 'profile_5',
  profile_6: 'profile_6',
  profile_7: 'profile_7',
  profile_8: 'profile_8',
  profile_9: 'profile_9',
  profile_10: 'profile_10',
  profile_11: 'profile_11',
  profile_12: 'profile_12',
  profile_13: 'profile_13',
  profile_14: 'profile_14',
  profile_15: 'profile_15',
  profile_16: 'profile_16',
  profile_17: 'profile_17',
  profile_18: 'profile_18',
  profile_19: 'profile_19',
  profile_20: 'profile_20',
  test: 'test'
};

exports.Prisma.Wp_newsletter_emailsScalarFieldEnum = {
  id: 'id',
  language: 'language',
  subject: 'subject',
  message: 'message',
  created: 'created',
  status: 'status',
  total: 'total',
  last_id: 'last_id',
  sent: 'sent',
  track: 'track',
  list: 'list',
  type: 'type',
  query: 'query',
  editor: 'editor',
  sex: 'sex',
  theme: 'theme',
  message_text: 'message_text',
  preferences: 'preferences',
  send_on: 'send_on',
  token: 'token',
  options: 'options',
  private: 'private',
  click_count: 'click_count',
  version: 'version',
  open_count: 'open_count',
  unsub_count: 'unsub_count',
  error_count: 'error_count',
  stats_time: 'stats_time',
  updated: 'updated'
};

exports.Prisma.Wp_newsletter_sentScalarFieldEnum = {
  email_id: 'email_id',
  user_id: 'user_id',
  status: 'status',
  open: 'open',
  time: 'time',
  error: 'error',
  ip: 'ip'
};

exports.Prisma.Wp_newsletter_statsScalarFieldEnum = {
  id: 'id',
  created: 'created',
  url: 'url',
  user_id: 'user_id',
  email_id: 'email_id',
  ip: 'ip'
};

exports.Prisma.Wp_newsletter_user_logsScalarFieldEnum = {
  id: 'id',
  user_id: 'user_id',
  ip: 'ip',
  source: 'source',
  data: 'data',
  created: 'created'
};

exports.Prisma.Wp_optionsScalarFieldEnum = {
  option_id: 'option_id',
  option_name: 'option_name',
  option_value: 'option_value',
  autoload: 'autoload'
};

exports.Prisma.Wp_postmetaScalarFieldEnum = {
  meta_id: 'meta_id',
  post_id: 'post_id',
  meta_key: 'meta_key',
  meta_value: 'meta_value'
};

exports.Prisma.Wp_postsScalarFieldEnum = {
  ID: 'ID',
  post_author: 'post_author',
  post_date: 'post_date',
  post_date_gmt: 'post_date_gmt',
  post_content: 'post_content',
  post_title: 'post_title',
  post_excerpt: 'post_excerpt',
  post_status: 'post_status',
  comment_status: 'comment_status',
  ping_status: 'ping_status',
  post_password: 'post_password',
  post_name: 'post_name',
  to_ping: 'to_ping',
  pinged: 'pinged',
  post_modified: 'post_modified',
  post_modified_gmt: 'post_modified_gmt',
  post_content_filtered: 'post_content_filtered',
  post_parent: 'post_parent',
  guid: 'guid',
  menu_order: 'menu_order',
  post_type: 'post_type',
  post_mime_type: 'post_mime_type',
  comment_count: 'comment_count'
};

exports.Prisma.Wp_redirection_404ScalarFieldEnum = {
  id: 'id',
  created: 'created',
  url: 'url',
  domain: 'domain',
  agent: 'agent',
  referrer: 'referrer',
  http_code: 'http_code',
  request_method: 'request_method',
  request_data: 'request_data',
  ip: 'ip'
};

exports.Prisma.Wp_redirection_groupsScalarFieldEnum = {
  id: 'id',
  name: 'name',
  tracking: 'tracking',
  module_id: 'module_id',
  status: 'status',
  position: 'position'
};

exports.Prisma.Wp_redirection_itemsScalarFieldEnum = {
  id: 'id',
  url: 'url',
  match_url: 'match_url',
  match_data: 'match_data',
  regex: 'regex',
  position: 'position',
  last_count: 'last_count',
  last_access: 'last_access',
  group_id: 'group_id',
  status: 'status',
  action_type: 'action_type',
  action_code: 'action_code',
  action_data: 'action_data',
  match_type: 'match_type',
  title: 'title'
};

exports.Prisma.Wp_redirection_logsScalarFieldEnum = {
  id: 'id',
  created: 'created',
  url: 'url',
  domain: 'domain',
  sent_to: 'sent_to',
  agent: 'agent',
  referrer: 'referrer',
  http_code: 'http_code',
  request_method: 'request_method',
  request_data: 'request_data',
  redirect_by: 'redirect_by',
  redirection_id: 'redirection_id',
  ip: 'ip'
};

exports.Prisma.Wp_rum_age_groupScalarFieldEnum = {
  id: 'id',
  name: 'name',
  lower_age: 'lower_age',
  upper_age: 'upper_age',
  sort: 'sort',
  deleted_at: 'deleted_at',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_rum_capabilityScalarFieldEnum = {
  id: 'id',
  name: 'name',
  icon: 'icon',
  sort: 'sort',
  deleted_at: 'deleted_at',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_rum_clubScalarFieldEnum = {
  id: 'id',
  name: 'name',
  image: 'image',
  sort: 'sort',
  deleted_at: 'deleted_at',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_rum_competencyScalarFieldEnum = {
  id: 'id',
  position_id: 'position_id',
  age_group_id: 'age_group_id',
  lower_limit: 'lower_limit',
  upper_limit: 'upper_limit',
  sort: 'sort',
  deleted_at: 'deleted_at',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_rum_highlightScalarFieldEnum = {
  id: 'id',
  player_id: 'player_id',
  video: 'video',
  name: 'name',
  video_id: 'video_id',
  source: 'source',
  sort: 'sort',
  deleted_at: 'deleted_at',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_rum_nationalityScalarFieldEnum = {
  id: 'id',
  name: 'name',
  sort: 'sort',
  deleted_at: 'deleted_at',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_rum_pdf_overflowScalarFieldEnum = {
  id: 'id',
  player_id: 'player_id',
  sponsor_id: 'sponsor_id',
  amount: 'amount',
  year: 'year',
  slot: 'slot',
  product_id: 'product_id',
  order_id: 'order_id',
  deleted_at: 'deleted_at',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_rum_personal_development_fundScalarFieldEnum = {
  id: 'id',
  player_id: 'player_id',
  sponsor_id: 'sponsor_id',
  admin_id: 'admin_id',
  amount: 'amount',
  year: 'year',
  slot: 'slot',
  sort: 'sort',
  deleted_at: 'deleted_at',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_rum_player_capabilityScalarFieldEnum = {
  id: 'id',
  player_id: 'player_id',
  capability_id: 'capability_id',
  ratting: 'ratting',
  sort: 'sort',
  deleted_at: 'deleted_at',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_rum_player_competencyScalarFieldEnum = {
  id: 'id',
  player_id: 'player_id',
  competency_id: 'competency_id',
  attack: 'attack',
  defence: 'defence',
  mental_condition: 'mental_condition',
  physical_condition: 'physical_condition',
  sort: 'sort',
  deleted_at: 'deleted_at',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_rum_positionScalarFieldEnum = {
  id: 'id',
  name: 'name',
  sort: 'sort',
  deleted_at: 'deleted_at',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_rum_reset_passwordScalarFieldEnum = {
  id: 'id',
  code: 'code',
  email: 'email',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_rum_rum_image_trackerScalarFieldEnum = {
  id: 'id',
  image_id: 'image_id',
  user_id: 'user_id',
  purpose: 'purpose',
  image_url: 'image_url',
  deleted_at: 'deleted_at',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_sp_answer_logScalarFieldEnum = {
  id: 'id',
  study_id: 'study_id',
  card_id: 'card_id',
  last_card_updated_at: 'last_card_updated_at',
  accepted_change_comment: 'accepted_change_comment',
  question: 'question',
  answer: 'answer',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_sp_answeredScalarFieldEnum = {
  id: 'id',
  study_id: 'study_id',
  card_id: 'card_id',
  answer: 'answer',
  question: 'question',
  grade: 'grade',
  ease_factor: 'ease_factor',
  next_due_at: 'next_due_at',
  next_due_answered: 'next_due_answered',
  started_at: 'started_at',
  answered_as_new: 'answered_as_new',
  answered_as_revised: 'answered_as_revised',
  next_interval: 'next_interval',
  rejected_at: 'rejected_at',
  deleted_at: 'deleted_at',
  created_at: 'created_at',
  updated_at: 'updated_at',
  card_last_updated_at: 'card_last_updated_at',
  accept_changes_comment: 'accept_changes_comment',
  answered_as_on_hold: 'answered_as_on_hold'
};

exports.Prisma.Wp_sp_card_groupsScalarFieldEnum = {
  id: 'id',
  deck_id: 'deck_id',
  bg_image_id: 'bg_image_id',
  name: 'name',
  whole_question: 'whole_question',
  card_type: 'card_type',
  scheduled_at: 'scheduled_at',
  reverse: 'reverse',
  image_type: 'image_type',
  deleted_at: 'deleted_at',
  created_at: 'created_at',
  updated_at: 'updated_at',
  collection_id: 'collection_id',
  topic_id: 'topic_id'
};

exports.Prisma.Wp_sp_cardsScalarFieldEnum = {
  id: 'id',
  card_group_id: 'card_group_id',
  hash: 'hash',
  c_number: 'c_number',
  question: 'question',
  answer: 'answer',
  x_position: 'x_position',
  y_position: 'y_position',
  deleted_at: 'deleted_at',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_sp_collectionsScalarFieldEnum = {
  id: 'id',
  name: 'name',
  deleted_at: 'deleted_at',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_sp_deck_groupsScalarFieldEnum = {
  id: 'id',
  name: 'name',
  deleted_at: 'deleted_at',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_sp_decksScalarFieldEnum = {
  id: 'id',
  name: 'name',
  deck_group_id: 'deck_group_id',
  deleted_at: 'deleted_at',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_sp_studyScalarFieldEnum = {
  id: 'id',
  deck_id: 'deck_id',
  user_id: 'user_id',
  all_tags: 'all_tags',
  no_to_revise: 'no_to_revise',
  no_of_new: 'no_of_new',
  no_on_hold: 'no_on_hold',
  active: 'active',
  revise_all: 'revise_all',
  study_all_new: 'study_all_new',
  study_all_on_hold: 'study_all_on_hold',
  deleted_at: 'deleted_at',
  created_at: 'created_at',
  updated_at: 'updated_at',
  topic_id: 'topic_id',
  deck_group_id: 'deck_group_id',
  active3: 'active3'
};

exports.Prisma.Wp_sp_study_logScalarFieldEnum = {
  id: 'id',
  study_id: 'study_id',
  card_id: 'card_id',
  action: 'action',
  created_at: 'created_at'
};

exports.Prisma.Wp_sp_taggablesScalarFieldEnum = {
  id: 'id',
  tag_id: 'tag_id',
  taggable_id: 'taggable_id',
  taggable_type: 'taggable_type',
  deleted_at: 'deleted_at',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_sp_taggables_excludedScalarFieldEnum = {
  id: 'id',
  tag_id: 'tag_id',
  taggable_id: 'taggable_id',
  taggable_type: 'taggable_type',
  deleted_at: 'deleted_at',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_sp_tagsScalarFieldEnum = {
  id: 'id',
  name: 'name',
  deleted_at: 'deleted_at',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_sp_topicsScalarFieldEnum = {
  id: 'id',
  name: 'name',
  deck_id: 'deck_id',
  deleted_at: 'deleted_at',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_sp_user_cardsScalarFieldEnum = {
  id: 'id',
  user_id: 'user_id',
  card_group_id: 'card_group_id',
  deleted_at: 'deleted_at',
  created_at: 'created_at',
  updated_at: 'updated_at'
};

exports.Prisma.Wp_term_relationshipsScalarFieldEnum = {
  object_id: 'object_id',
  term_taxonomy_id: 'term_taxonomy_id',
  term_order: 'term_order'
};

exports.Prisma.Wp_term_taxonomyScalarFieldEnum = {
  term_taxonomy_id: 'term_taxonomy_id',
  term_id: 'term_id',
  taxonomy: 'taxonomy',
  description: 'description',
  parent: 'parent',
  count: 'count'
};

exports.Prisma.Wp_termmetaScalarFieldEnum = {
  meta_id: 'meta_id',
  term_id: 'term_id',
  meta_key: 'meta_key',
  meta_value: 'meta_value'
};

exports.Prisma.Wp_termsScalarFieldEnum = {
  term_id: 'term_id',
  name: 'name',
  slug: 'slug',
  term_group: 'term_group'
};

exports.Prisma.Wp_usermetaScalarFieldEnum = {
  umeta_id: 'umeta_id',
  user_id: 'user_id',
  meta_key: 'meta_key',
  meta_value: 'meta_value'
};

exports.Prisma.Wp_usersScalarFieldEnum = {
  ID: 'ID',
  user_login: 'user_login',
  user_pass: 'user_pass',
  user_nicename: 'user_nicename',
  user_email: 'user_email',
  user_url: 'user_url',
  user_registered: 'user_registered',
  user_activation_key: 'user_activation_key',
  user_status: 'user_status',
  display_name: 'display_name'
};

exports.Prisma.Wp_wc_admin_note_actionsScalarFieldEnum = {
  action_id: 'action_id',
  note_id: 'note_id',
  name: 'name',
  label: 'label',
  query: 'query',
  status: 'status',
  is_primary: 'is_primary',
  actioned_text: 'actioned_text',
  nonce_action: 'nonce_action',
  nonce_name: 'nonce_name'
};

exports.Prisma.Wp_wc_admin_notesScalarFieldEnum = {
  note_id: 'note_id',
  name: 'name',
  type: 'type',
  locale: 'locale',
  title: 'title',
  content: 'content',
  content_data: 'content_data',
  status: 'status',
  source: 'source',
  date_created: 'date_created',
  date_reminder: 'date_reminder',
  is_snoozable: 'is_snoozable',
  layout: 'layout',
  image: 'image',
  is_deleted: 'is_deleted',
  is_read: 'is_read',
  icon: 'icon'
};

exports.Prisma.Wp_wc_category_lookupScalarFieldEnum = {
  category_tree_id: 'category_tree_id',
  category_id: 'category_id'
};

exports.Prisma.Wp_wc_customer_lookupScalarFieldEnum = {
  customer_id: 'customer_id',
  user_id: 'user_id',
  username: 'username',
  first_name: 'first_name',
  last_name: 'last_name',
  email: 'email',
  date_last_active: 'date_last_active',
  date_registered: 'date_registered',
  country: 'country',
  postcode: 'postcode',
  city: 'city',
  state: 'state'
};

exports.Prisma.Wp_wc_download_logScalarFieldEnum = {
  download_log_id: 'download_log_id',
  timestamp: 'timestamp',
  permission_id: 'permission_id',
  user_id: 'user_id',
  user_ip_address: 'user_ip_address'
};

exports.Prisma.Wp_wc_order_coupon_lookupScalarFieldEnum = {
  order_id: 'order_id',
  coupon_id: 'coupon_id',
  date_created: 'date_created',
  discount_amount: 'discount_amount'
};

exports.Prisma.Wp_wc_order_product_lookupScalarFieldEnum = {
  order_item_id: 'order_item_id',
  order_id: 'order_id',
  product_id: 'product_id',
  variation_id: 'variation_id',
  customer_id: 'customer_id',
  date_created: 'date_created',
  product_qty: 'product_qty',
  product_net_revenue: 'product_net_revenue',
  product_gross_revenue: 'product_gross_revenue',
  coupon_amount: 'coupon_amount',
  tax_amount: 'tax_amount',
  shipping_amount: 'shipping_amount',
  shipping_tax_amount: 'shipping_tax_amount'
};

exports.Prisma.Wp_wc_order_statsScalarFieldEnum = {
  order_id: 'order_id',
  parent_id: 'parent_id',
  date_created: 'date_created',
  date_created_gmt: 'date_created_gmt',
  num_items_sold: 'num_items_sold',
  total_sales: 'total_sales',
  tax_total: 'tax_total',
  shipping_total: 'shipping_total',
  net_total: 'net_total',
  returning_customer: 'returning_customer',
  status: 'status',
  customer_id: 'customer_id'
};

exports.Prisma.Wp_wc_order_tax_lookupScalarFieldEnum = {
  order_id: 'order_id',
  tax_rate_id: 'tax_rate_id',
  date_created: 'date_created',
  shipping_tax: 'shipping_tax',
  order_tax: 'order_tax',
  total_tax: 'total_tax'
};

exports.Prisma.Wp_wc_product_meta_lookupScalarFieldEnum = {
  product_id: 'product_id',
  sku: 'sku',
  virtual: 'virtual',
  downloadable: 'downloadable',
  min_price: 'min_price',
  max_price: 'max_price',
  onsale: 'onsale',
  stock_quantity: 'stock_quantity',
  stock_status: 'stock_status',
  rating_count: 'rating_count',
  average_rating: 'average_rating',
  total_sales: 'total_sales',
  tax_status: 'tax_status',
  tax_class: 'tax_class'
};

exports.Prisma.Wp_wc_rate_limitsScalarFieldEnum = {
  rate_limit_id: 'rate_limit_id',
  rate_limit_key: 'rate_limit_key',
  rate_limit_expiry: 'rate_limit_expiry'
};

exports.Prisma.Wp_wc_reserved_stockScalarFieldEnum = {
  order_id: 'order_id',
  product_id: 'product_id',
  stock_quantity: 'stock_quantity',
  timestamp: 'timestamp',
  expires: 'expires'
};

exports.Prisma.Wp_wc_tax_rate_classesScalarFieldEnum = {
  tax_rate_class_id: 'tax_rate_class_id',
  name: 'name',
  slug: 'slug'
};

exports.Prisma.Wp_wc_webhooksScalarFieldEnum = {
  webhook_id: 'webhook_id',
  status: 'status',
  name: 'name',
  user_id: 'user_id',
  delivery_url: 'delivery_url',
  secret: 'secret',
  topic: 'topic',
  date_created: 'date_created',
  date_created_gmt: 'date_created_gmt',
  date_modified: 'date_modified',
  date_modified_gmt: 'date_modified_gmt',
  api_version: 'api_version',
  failure_count: 'failure_count',
  pending_delivery: 'pending_delivery'
};

exports.Prisma.Wp_wcpdf_invoice_numberScalarFieldEnum = {
  id: 'id',
  order_id: 'order_id',
  date: 'date',
  calculated_number: 'calculated_number'
};

exports.Prisma.Wp_wcpdf_packing_slip_numberScalarFieldEnum = {
  id: 'id',
  order_id: 'order_id',
  date: 'date',
  calculated_number: 'calculated_number'
};

exports.Prisma.Wp_woocommerce_api_keysScalarFieldEnum = {
  key_id: 'key_id',
  user_id: 'user_id',
  description: 'description',
  permissions: 'permissions',
  consumer_key: 'consumer_key',
  consumer_secret: 'consumer_secret',
  nonces: 'nonces',
  truncated_key: 'truncated_key',
  last_access: 'last_access'
};

exports.Prisma.Wp_woocommerce_attribute_taxonomiesScalarFieldEnum = {
  attribute_id: 'attribute_id',
  attribute_name: 'attribute_name',
  attribute_label: 'attribute_label',
  attribute_type: 'attribute_type',
  attribute_orderby: 'attribute_orderby',
  attribute_public: 'attribute_public'
};

exports.Prisma.Wp_woocommerce_downloadable_product_permissionsScalarFieldEnum = {
  permission_id: 'permission_id',
  download_id: 'download_id',
  product_id: 'product_id',
  order_id: 'order_id',
  order_key: 'order_key',
  user_email: 'user_email',
  user_id: 'user_id',
  downloads_remaining: 'downloads_remaining',
  access_granted: 'access_granted',
  access_expires: 'access_expires',
  download_count: 'download_count'
};

exports.Prisma.Wp_woocommerce_logScalarFieldEnum = {
  log_id: 'log_id',
  timestamp: 'timestamp',
  level: 'level',
  source: 'source',
  message: 'message',
  context: 'context'
};

exports.Prisma.Wp_woocommerce_order_itemmetaScalarFieldEnum = {
  meta_id: 'meta_id',
  order_item_id: 'order_item_id',
  meta_key: 'meta_key',
  meta_value: 'meta_value'
};

exports.Prisma.Wp_woocommerce_order_itemsScalarFieldEnum = {
  order_item_id: 'order_item_id',
  order_item_name: 'order_item_name',
  order_item_type: 'order_item_type',
  order_id: 'order_id'
};

exports.Prisma.Wp_woocommerce_payment_tokenmetaScalarFieldEnum = {
  meta_id: 'meta_id',
  payment_token_id: 'payment_token_id',
  meta_key: 'meta_key',
  meta_value: 'meta_value'
};

exports.Prisma.Wp_woocommerce_payment_tokensScalarFieldEnum = {
  token_id: 'token_id',
  gateway_id: 'gateway_id',
  token: 'token',
  user_id: 'user_id',
  type: 'type',
  is_default: 'is_default'
};

exports.Prisma.Wp_woocommerce_sessionsScalarFieldEnum = {
  session_id: 'session_id',
  session_key: 'session_key',
  session_value: 'session_value',
  session_expiry: 'session_expiry'
};

exports.Prisma.Wp_woocommerce_shipping_zone_locationsScalarFieldEnum = {
  location_id: 'location_id',
  zone_id: 'zone_id',
  location_code: 'location_code',
  location_type: 'location_type'
};

exports.Prisma.Wp_woocommerce_shipping_zone_methodsScalarFieldEnum = {
  zone_id: 'zone_id',
  instance_id: 'instance_id',
  method_id: 'method_id',
  method_order: 'method_order',
  is_enabled: 'is_enabled'
};

exports.Prisma.Wp_woocommerce_shipping_zonesScalarFieldEnum = {
  zone_id: 'zone_id',
  zone_name: 'zone_name',
  zone_order: 'zone_order'
};

exports.Prisma.Wp_woocommerce_tax_rate_locationsScalarFieldEnum = {
  location_id: 'location_id',
  location_code: 'location_code',
  tax_rate_id: 'tax_rate_id',
  location_type: 'location_type'
};

exports.Prisma.Wp_woocommerce_tax_ratesScalarFieldEnum = {
  tax_rate_id: 'tax_rate_id',
  tax_rate_country: 'tax_rate_country',
  tax_rate_state: 'tax_rate_state',
  tax_rate: 'tax_rate',
  tax_rate_name: 'tax_rate_name',
  tax_rate_priority: 'tax_rate_priority',
  tax_rate_compound: 'tax_rate_compound',
  tax_rate_shipping: 'tax_rate_shipping',
  tax_rate_order: 'tax_rate_order',
  tax_rate_class: 'tax_rate_class'
};

exports.Prisma.Wp_woodmart_wishlist_productsScalarFieldEnum = {
  ID: 'ID',
  product_id: 'product_id',
  wishlist_id: 'wishlist_id',
  date_added: 'date_added'
};

exports.Prisma.Wp_woodmart_wishlistsScalarFieldEnum = {
  ID: 'ID',
  user_id: 'user_id',
  date_created: 'date_created'
};

exports.Prisma.Wp_wp_phpmyadmin_extension__errors_logScalarFieldEnum = {
  id: 'id',
  gmdate: 'gmdate',
  function_name: 'function_name',
  function_args: 'function_args',
  message: 'message'
};

exports.Prisma.SortOrder = {
  asc: 'asc',
  desc: 'desc'
};

exports.Prisma.NullsOrder = {
  first: 'first',
  last: 'last'
};
exports.wp_newsletter_emails_status = exports.$Enums.wp_newsletter_emails_status = {
  new: 'new',
  sending: 'sending',
  sent: 'sent',
  paused: 'paused',
  error: 'error'
};

exports.wp_redirection_groups_status = exports.$Enums.wp_redirection_groups_status = {
  enabled: 'enabled',
  disabled: 'disabled'
};

exports.wp_redirection_items_status = exports.$Enums.wp_redirection_items_status = {
  enabled: 'enabled',
  disabled: 'disabled'
};

exports.Prisma.ModelName = {
  deck_groups: 'deck_groups',
  phinxlog: 'phinxlog',
  wp_a_mp_knowledge_based_search_product_catalogue: 'wp_a_mp_knowledge_based_search_product_catalogue',
  wp_a_mp_knowledge_based_search_sql: 'wp_a_mp_knowledge_based_search_sql',
  wp_actionscheduler_actions: 'wp_actionscheduler_actions',
  wp_actionscheduler_claims: 'wp_actionscheduler_claims',
  wp_actionscheduler_groups: 'wp_actionscheduler_groups',
  wp_actionscheduler_logs: 'wp_actionscheduler_logs',
  wp_arm_free_members: 'wp_arm_free_members',
  wp_awesome_auction_password_reset: 'wp_awesome_auction_password_reset',
  wp_aysquiz_answers: 'wp_aysquiz_answers',
  wp_aysquiz_categories: 'wp_aysquiz_categories',
  wp_aysquiz_questions: 'wp_aysquiz_questions',
  wp_aysquiz_quizcategories: 'wp_aysquiz_quizcategories',
  wp_aysquiz_quizes: 'wp_aysquiz_quizes',
  wp_aysquiz_rates: 'wp_aysquiz_rates',
  wp_aysquiz_reports: 'wp_aysquiz_reports',
  wp_aysquiz_settings: 'wp_aysquiz_settings',
  wp_aysquiz_themes: 'wp_aysquiz_themes',
  wp_commentmeta: 'wp_commentmeta',
  wp_comments: 'wp_comments',
  wp_e_events: 'wp_e_events',
  wp_fbv: 'wp_fbv',
  wp_fbv_attachment_folder: 'wp_fbv_attachment_folder',
  wp_ff_scheduled_actions: 'wp_ff_scheduled_actions',
  wp_fluentform_draft_submissions: 'wp_fluentform_draft_submissions',
  wp_fluentform_entry_details: 'wp_fluentform_entry_details',
  wp_fluentform_form_analytics: 'wp_fluentform_form_analytics',
  wp_fluentform_form_meta: 'wp_fluentform_form_meta',
  wp_fluentform_forms: 'wp_fluentform_forms',
  wp_fluentform_logs: 'wp_fluentform_logs',
  wp_fluentform_order_items: 'wp_fluentform_order_items',
  wp_fluentform_submission_meta: 'wp_fluentform_submission_meta',
  wp_fluentform_submissions: 'wp_fluentform_submissions',
  wp_fluentform_subscriptions: 'wp_fluentform_subscriptions',
  wp_fluentform_transactions: 'wp_fluentform_transactions',
  wp_frm_fields: 'wp_frm_fields',
  wp_frm_forms: 'wp_frm_forms',
  wp_frm_item_metas: 'wp_frm_item_metas',
  wp_frm_items: 'wp_frm_items',
  wp_h5p_contents: 'wp_h5p_contents',
  wp_h5p_contents_libraries: 'wp_h5p_contents_libraries',
  wp_h5p_contents_tags: 'wp_h5p_contents_tags',
  wp_h5p_contents_user_data: 'wp_h5p_contents_user_data',
  wp_h5p_counters: 'wp_h5p_counters',
  wp_h5p_events: 'wp_h5p_events',
  wp_h5p_libraries: 'wp_h5p_libraries',
  wp_h5p_libraries_cachedassets: 'wp_h5p_libraries_cachedassets',
  wp_h5p_libraries_hub_cache: 'wp_h5p_libraries_hub_cache',
  wp_h5p_libraries_languages: 'wp_h5p_libraries_languages',
  wp_h5p_libraries_libraries: 'wp_h5p_libraries_libraries',
  wp_h5p_results: 'wp_h5p_results',
  wp_h5p_tags: 'wp_h5p_tags',
  wp_h5p_tmpfiles: 'wp_h5p_tmpfiles',
  wp_links: 'wp_links',
  wp_lte_device_serial_data: 'wp_lte_device_serial_data',
  wp_mp_student_teacher_manager_attendance: 'wp_mp_student_teacher_manager_attendance',
  wp_mp_student_teacher_manager_bills: 'wp_mp_student_teacher_manager_bills',
  wp_mp_student_teacher_manager_issued_bills: 'wp_mp_student_teacher_manager_issued_bills',
  wp_mp_student_teacher_manager_options: 'wp_mp_student_teacher_manager_options',
  wp_mp_student_teacher_manager_point_types: 'wp_mp_student_teacher_manager_point_types',
  wp_mp_student_teacher_manager_points: 'wp_mp_student_teacher_manager_points',
  wp_mp_student_teacher_manager_products: 'wp_mp_student_teacher_manager_products',
  wp_mp_student_teacher_manager_products_bought: 'wp_mp_student_teacher_manager_products_bought',
  wp_mp_student_teacher_manager_teachers_students: 'wp_mp_student_teacher_manager_teachers_students',
  wp_mp_student_teacher_manager_user_image: 'wp_mp_student_teacher_manager_user_image',
  wp_mycred_log: 'wp_mycred_log',
  wp_newsletter: 'wp_newsletter',
  wp_newsletter_emails: 'wp_newsletter_emails',
  wp_newsletter_sent: 'wp_newsletter_sent',
  wp_newsletter_stats: 'wp_newsletter_stats',
  wp_newsletter_user_logs: 'wp_newsletter_user_logs',
  wp_options: 'wp_options',
  wp_postmeta: 'wp_postmeta',
  wp_posts: 'wp_posts',
  wp_redirection_404: 'wp_redirection_404',
  wp_redirection_groups: 'wp_redirection_groups',
  wp_redirection_items: 'wp_redirection_items',
  wp_redirection_logs: 'wp_redirection_logs',
  wp_rum_age_group: 'wp_rum_age_group',
  wp_rum_capability: 'wp_rum_capability',
  wp_rum_club: 'wp_rum_club',
  wp_rum_competency: 'wp_rum_competency',
  wp_rum_highlight: 'wp_rum_highlight',
  wp_rum_nationality: 'wp_rum_nationality',
  wp_rum_pdf_overflow: 'wp_rum_pdf_overflow',
  wp_rum_personal_development_fund: 'wp_rum_personal_development_fund',
  wp_rum_player_capability: 'wp_rum_player_capability',
  wp_rum_player_competency: 'wp_rum_player_competency',
  wp_rum_position: 'wp_rum_position',
  wp_rum_reset_password: 'wp_rum_reset_password',
  wp_rum_rum_image_tracker: 'wp_rum_rum_image_tracker',
  wp_sp_answer_log: 'wp_sp_answer_log',
  wp_sp_answered: 'wp_sp_answered',
  wp_sp_card_groups: 'wp_sp_card_groups',
  wp_sp_cards: 'wp_sp_cards',
  wp_sp_collections: 'wp_sp_collections',
  wp_sp_deck_groups: 'wp_sp_deck_groups',
  wp_sp_decks: 'wp_sp_decks',
  wp_sp_study: 'wp_sp_study',
  wp_sp_study_log: 'wp_sp_study_log',
  wp_sp_taggables: 'wp_sp_taggables',
  wp_sp_taggables_excluded: 'wp_sp_taggables_excluded',
  wp_sp_tags: 'wp_sp_tags',
  wp_sp_topics: 'wp_sp_topics',
  wp_sp_user_cards: 'wp_sp_user_cards',
  wp_term_relationships: 'wp_term_relationships',
  wp_term_taxonomy: 'wp_term_taxonomy',
  wp_termmeta: 'wp_termmeta',
  wp_terms: 'wp_terms',
  wp_usermeta: 'wp_usermeta',
  wp_users: 'wp_users',
  wp_wc_admin_note_actions: 'wp_wc_admin_note_actions',
  wp_wc_admin_notes: 'wp_wc_admin_notes',
  wp_wc_category_lookup: 'wp_wc_category_lookup',
  wp_wc_customer_lookup: 'wp_wc_customer_lookup',
  wp_wc_download_log: 'wp_wc_download_log',
  wp_wc_order_coupon_lookup: 'wp_wc_order_coupon_lookup',
  wp_wc_order_product_lookup: 'wp_wc_order_product_lookup',
  wp_wc_order_stats: 'wp_wc_order_stats',
  wp_wc_order_tax_lookup: 'wp_wc_order_tax_lookup',
  wp_wc_product_meta_lookup: 'wp_wc_product_meta_lookup',
  wp_wc_rate_limits: 'wp_wc_rate_limits',
  wp_wc_reserved_stock: 'wp_wc_reserved_stock',
  wp_wc_tax_rate_classes: 'wp_wc_tax_rate_classes',
  wp_wc_webhooks: 'wp_wc_webhooks',
  wp_wcpdf_invoice_number: 'wp_wcpdf_invoice_number',
  wp_wcpdf_packing_slip_number: 'wp_wcpdf_packing_slip_number',
  wp_woocommerce_api_keys: 'wp_woocommerce_api_keys',
  wp_woocommerce_attribute_taxonomies: 'wp_woocommerce_attribute_taxonomies',
  wp_woocommerce_downloadable_product_permissions: 'wp_woocommerce_downloadable_product_permissions',
  wp_woocommerce_log: 'wp_woocommerce_log',
  wp_woocommerce_order_itemmeta: 'wp_woocommerce_order_itemmeta',
  wp_woocommerce_order_items: 'wp_woocommerce_order_items',
  wp_woocommerce_payment_tokenmeta: 'wp_woocommerce_payment_tokenmeta',
  wp_woocommerce_payment_tokens: 'wp_woocommerce_payment_tokens',
  wp_woocommerce_sessions: 'wp_woocommerce_sessions',
  wp_woocommerce_shipping_zone_locations: 'wp_woocommerce_shipping_zone_locations',
  wp_woocommerce_shipping_zone_methods: 'wp_woocommerce_shipping_zone_methods',
  wp_woocommerce_shipping_zones: 'wp_woocommerce_shipping_zones',
  wp_woocommerce_tax_rate_locations: 'wp_woocommerce_tax_rate_locations',
  wp_woocommerce_tax_rates: 'wp_woocommerce_tax_rates',
  wp_woodmart_wishlist_products: 'wp_woodmart_wishlist_products',
  wp_woodmart_wishlists: 'wp_woodmart_wishlists',
  wp_wp_phpmyadmin_extension__errors_log: 'wp_wp_phpmyadmin_extension__errors_log'
};

/**
 * This is a stub Prisma Client that will error at runtime if called.
 */
class PrismaClient {
  constructor() {
    return new Proxy(this, {
      get(target, prop) {
        let message
        const runtime = getRuntime()
        if (runtime.isEdge) {
          message = `PrismaClient is not configured to run in ${runtime.prettyName}. In order to run Prisma Client on edge runtime, either:
- Use Prisma Accelerate: https://pris.ly/d/accelerate
- Use Driver Adapters: https://pris.ly/d/driver-adapters
`;
        } else {
          message = 'PrismaClient is unable to run in this browser environment, or has been bundled for the browser (running in `' + runtime.prettyName + '`).'
        }
        
        message += `
If this is unexpected, please open an issue: https://pris.ly/prisma-prisma-bug-report`

        throw new Error(message)
      }
    })
  }
}

exports.PrismaClient = PrismaClient

Object.assign(exports, Prisma)
